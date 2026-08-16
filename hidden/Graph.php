<?php
require_once "Trend.php";

class Graph
{
    private DataService $ds;
    public int $year;
    public string $dataset;
    public ?Variable $mainVariable;
    public ?Variable $groupingVariable = null;
    public array $percentData = []; //the data structure used by AmCharts to generate a graph
    public int $graphHeight; //height of the graph in pixels
    public float $noResponse; //number of surveys that didn't answer this question
    public float $sumTotal; //number of surveys that did answer the question
    public array $sumPositives = []; //number of surveys that answered positively
    public bool $belowThreshold = false; //true if the count in any cell is below the anonymity threshold
    public bool $mainVarUnavailable = false; //true if the variable is not in the dataset for this year
    public bool $groupingVarUnavailable = false;

    //Trend variables
    public string $title;
    public ?string $trendName = null;
    public ?int $trendGroup = null;
    public array $yearsInGraph = []; //Trend graph x-axis labels
    public array $yearlyTotals = []; //Trend graph
    public array $assetLabels = [0,1,2,3,4,5,6]; //3TS graph x-axis labels
    public array $assetTotals = []; //3TS x-axis valid cases (the Trend and 3TS variables could be merged)
    public array $labels = [];
    public string $notes;
    public bool $hasSuppression = false; //true if a cell in the data table has been suppressed

    public array $sumTotals = []; //sum valid cases for a variable
    public array $tooltips = []; //mouse-over pop-ups to explain graph labels and bars

    //Filter variables
    public ?int $gradeFilter;
    public ?int $genderFilter;
    public ?int $raceFilter;
    public ?int $sexOrientationFilter;
    public ?int $pyramidFilter;
    public ?int $raceSimplifiedFilter;
    public ?int $numAssetsFilter;
    public ?int $transgenderFilter;
    public ?int $disabilityFilter;

    /**
     * @param int $year
     * @param string $dataset
     */
    public function __construct(int $year, string $dataset)
    {
        $this->ds = DataService::getInstance($year, $dataset);
        $this->year = $year;
        $this->dataset = $dataset;
    }

    private function getVariableAndLoadData(string $code) {
        $filter = 1;
        $variable = $this->ds->getCutoffVariable($code);
        $variable->initializeCounts($this->groupingVariable);
        $this->ds->getCutoffPositives($variable, $this->groupingVariable, $filter);
        $this->ds->getCutoffTotal($variable, $this->groupingVariable, $filter);
        $variable->calculatePercents();
    }

    /**
     * Create the data structure for a graph containing multiple CutoffVariables (the highlight group).
     * @param int $year
     * @param string $dataset
     * @param int|null $category
     * @param string|null $groupCode Group each CutoffVariable by a demographic variable.
     * @return Graph|null
     * @throws Exception
     */
    public static function createHighlightsGraph(int $year, string $dataset, ?int $category, ?string $groupCode) : ?Graph
    {
        $graph = new Graph($year, $dataset);

        $graph->groupingVariable = $graph->ds->getVariable($groupCode);
        if($graph->groupingVariable != null)
            $graph->groupingVariable->labels[] = "Total"; //Add a "Total" answer option. This is where the ROLLUP value will go.
        $variablesInGraph = [];
        $filter = "1";

        $highlightGroup = getHighlightGroup($category, $dataset, $year);

        //get data for each question
        for($i = 0; $i < count($highlightGroup->codes); $i++)
        {
            $variable = $graph->ds->getCutoffVariable($highlightGroup->codes[$i]);
            if($variable == null)
                continue;
            $variable->initializeCounts($graph->groupingVariable);
            $graph->ds->getCutoffPositives($variable, $graph->groupingVariable, $filter, true);
            $graph->ds->getCutoffTotal($variable, $graph->groupingVariable, $filter, true);
            $variable->calculatePercents();
            $variablesInGraph[] = $variable;
        }

        //Create the data structure used by AmCharts for bar graphs
        //[['answer' => Var1 label, 'v0' => Group0 percent, 'v1' => Group1 percent, ...], ['answer' => Var 2 label, ...]]
        $graph->percentData = [];
        foreach ($variablesInGraph as $variable) {
            $percentArray['answer'] = $variable->cutoff_summary;
            for($i=0; $i<count($variable->counts); $i++) {
                $percentArray['v'.$i] = $variable->percents[$i];
            }
            $graph->percentData[] = $percentArray;
        }

        //Also create data for display in graph and table
        $graph->mainVariable = new Variable(); //create a dummy variable to store data
        foreach ($variablesInGraph as $variable) {
            $graph->mainVariable->labels[] = $variable->cutoff_summary;
            $graph->mainVariable->counts[] = $variable->counts;
            $graph->sumPositives[] = array_sum($variable->counts);
            if($graph->groupingVariable !== null) //If not grouping, sum the totals. If grouping, use the ROLLUP (otherwise the total is doubled)
                $graph->sumTotals[] = end($variable->totals); //last element is the ROLLUP
            else
                $graph->sumTotals[] = array_sum($variable->totals);
            $graph->tooltips[] = $variable->cutoff_tooltip;
        }

        //height is (labels*(labels+spacing)*bar height + header height
        $numGroupLabels = ($graph->groupingVariable != null) ? count($graph->groupingVariable->labels) : 1;
        $graph->graphHeight = min(900,max(600,($numGroupLabels+1)*count($highlightGroup->codes)*30+100));
        return $graph;
    }

    /**
     * @param string $yearRange
     * @param string $dataset
     * @param string $mainVarCode
     * @param string|null $groupCode
     * @param int|null $pyramid
     * @return Graph|null
     * @throws Exception
     */
    public static function createTrendsGraph(string $yearRange, string $dataset, string $mainVarCode, ?string $groupCode, ?int $pyramid) : ?Graph
    {
        $graph = new Graph(getCurrentYear(), $dataset);

        //Cut down the year range to be either pre-2024 or post-2025
        $years = getAllYears();
        if($yearRange == Trend::PRE_2024)
            $graph->yearsInGraph = array_slice($years, 0, array_search(2025, $years));
        else
            $graph->yearsInGraph = array_slice($years, array_search(2025, $years));

        //Each variable has a separate version of it for each year (since the text might change slightly)
        $mainVariables = $graph->ds->getVariablesInTrend($mainVarCode);

        //We only want the variable instances that are in the chosen year range
        $mainVariableByYear = [];
        foreach ($mainVariables as $variable) {
            if(in_array($variable->year, $years))
                $mainVariableByYear[$variable->year] = $variable;
        }

        //Assign the most recent year's variable as the main variable (so that we can use its summary and labels)
        $graph->mainVariable = end($mainVariableByYear);

        //Do the same for the grouping variable
        if($groupCode) {
            $groupVariables = $graph->ds->getVariablesInTrend($groupCode);
            $groupVariableByYear = [];
            foreach ($groupVariables as $variable) {
                if (in_array($variable->year, $years))
                    $groupVariableByYear[$variable->year] = $variable;
            }
            //Get all of the grouping variable's data because we need the labels
            $ds = DataService::getInstance(end($groupVariableByYear)->year, $dataset);
            $graph->groupingVariable = $ds->getVariable($groupCode);
        }

        $filter = $graph->addFilter(null, null, null, null, $pyramid, null, null, null, null);

        //Get the data for each year
        $yearsActuallyInGraph = []; //the variables might be unavailable for some years
        foreach ($graph->yearsInGraph as $year)
        {
            $ds = DataService::getInstance($year, $dataset);
            $yearData = ["answer" => $year];

            //If the main or group variable is not in this year's dataset, skip this year's data
            if(!array_key_exists($year, $mainVariableByYear) || ($groupCode && !array_key_exists($year, $groupVariableByYear)) ) {
                $yearData['v0'] = null;
            }
            //Otherwise, get this year's variable and calculate its values
            else {
                $yearsActuallyInGraph[] = $year;
                $variable = $mainVariableByYear[$year];
                $groupVar = ($groupCode) ? $groupVariableByYear[$year] : null;
                $variable->initializeCounts($groupVar);
                $ds->getCutoffPositives($variable, $groupVar, $filter);
                $ds->getCutoffTotal($variable, $groupVar, $filter);
                for ($i = 0; $i < count($variable->counts); $i++)
                    $yearData['v'.$i] = round($variable->getPercent($i+1) * 100, 1);

                //non-binary doesn't exist in <2022 surveys
                if($groupCode == "gender_nb" && $year < 2022)
                    $yearData['v2'] = null;

                $graph->percentData[] = $yearData;
                $graph->yearlyTotals[] = count($variable->totals) === 0 ? null : array_sum($variable->totals);
            }
        }
        $graph->yearsInGraph = $yearsActuallyInGraph;

        //If the graph was grouped, there's a line (label) for each group var answer option. Otherwise, there's one line
        $graph->labels = $groupCode ? $graph->groupingVariable->labels : [$graph->mainVariable->cutoff_summary];
        $graph->notes = VariableNotes::getNotes($mainVarCode);
        $graph->graphHeight = 700;
        return $graph;
    }

    /**
     * @param int $year
     * @param string $dataset
     * @param string $mainVarCode
     * @param string|null $groupVarCode
     * @param int|null $gradeFilter
     * @param int|null $genderFilter
     * @param int|null $raceFilter
     * @param int|null $sexOrientationFilter
     * @param int|null $pyramidFilter
     * @param int|null $raceSimplifiedFilter
     * @param int|null $transgenderFilter
     * @param int|null $disabilityFilter
     * @return Graph|null
     * @throws Exception
     */
    public static function createExploreGraph(int  $year, string $dataset, string $mainVarCode, ?string $groupVarCode, ?int $gradeFilter, ?int $genderFilter, ?int $raceFilter,
                                              ?int $sexOrientationFilter, ?int $pyramidFilter, ?int $raceSimplifiedFilter, ?int $transgenderFilter, ?int $disabilityFilter) : ?Graph
    {
        $graph = new Graph($year, $dataset);

        //check if these variables are missing from this year's dataset
        $graph->mainVarUnavailable = !$graph->ds->isVariableInData($mainVarCode);
        if($groupVarCode != null)
            $graph->groupingVarUnavailable = !$graph->ds->isVariableInData($groupVarCode);
        if($graph->mainVarUnavailable || $graph->groupingVarUnavailable)
            return $graph;

        $graph->mainVariable = $graph->ds->getVariable($mainVarCode);
        if($graph->ds->isUnweighted($mainVarCode))
            $graph->mainVariable->question .= " (Data are unweighted)";
        $graph->groupingVariable = $graph->ds->getVariable($groupVarCode);

        //if using Pyramid, set erase some of the other filters
        if($pyramidFilter > 0) {
            $raceFilter = null;
            $sexOrientationFilter = null;
        }
        $filter = $graph->addFilter($gradeFilter, $genderFilter, $raceFilter, $sexOrientationFilter, $pyramidFilter, $raceSimplifiedFilter,
            null, $transgenderFilter, $disabilityFilter);

        //Load data into main Variable
        $graph->mainVariable->initializeCounts($graph->groupingVariable);
        $graph->ds->getMultiPositives($graph->mainVariable, $graph->groupingVariable, $filter);
        $graph->ds->getMultiTotals($graph->mainVariable, $graph->groupingVariable, $filter);
        $graph->belowThreshold = $graph->ds->checkAnonymityThreshold($graph->mainVariable, $graph->groupingVariable);
        $graph->mainVariable->calculatePercents();

        //Group variables NOT SURE IF NEEDED
        /*if ($groupVar != null) {
            $groupLabels = $groupVar->getLabels();
            $groupSummary = $groupVar->summary;
            $groupQuestion = $groupVar->question;
        } else {
            $groupLabels = ['Total'];
            $groupSummary = null;
            $groupQuestion = null;
        }*/

        //Create the data structure used by AmCharts for bar graphs
        //[['answer' => Var1 label, 'v0' => Group0 percent, 'v1' => Group1 percent, ...], ['answer' => Var 2 label, ...]]
        $graph->percentData = [];
        $numGroupLabels = ($graph->groupingVariable != null) ? count($graph->groupingVariable->labels) : 1;
        for ($i=0; $i < count($graph->mainVariable->labels); $i++) {
            $percentArray['answer'] = $graph->mainVariable->labels[$i];
            for($j=0; $j < $numGroupLabels; $j++) {
                $percentArray['v'.$j] = $graph->mainVariable->percents[$i][$j];
            }
            $graph->percentData[] = $percentArray;
        }

        //Calculate other values for graph and data table
        $graph->graphHeight = min(900, max(600, ($numGroupLabels + 1) * count($graph->mainVariable->labels) * 30 + 100)); //height is (labels*(labels+spacing)*bar height + header height
        $graph->noResponse = $graph->ds->getNoResponseCount($graph->mainVariable, $graph->groupingVariable, $filter);
        $graph->sumTotal = $graph->mainVariable->getSumTotal();
        $graph->sumPositives = $graph->mainVariable->getSumPositives();
        $graph->notes = VariableNotes::getNotes($mainVarCode);

        return $graph;
    }

    /**
     * @param int $year
     * @param string $dataset
     * @param string $mainVarCode
     * @param string|null $groupCode
     * @param int|null $pyramid
     * @return Graph|null
     * @throws Exception
     */
    public static function createThreeToSucceedGraph(int $year, string $dataset, string $mainVarCode, ?string $groupCode, ?int $pyramid) : ?Graph
    {
        $graph = new Graph($year, $dataset);

        //nonbinary doesn't exist in < 2022 surveys ALTERNATE WAY OF DOING IT
        //if($groupCode == "gender_nb" && $year < 2022)
        //    $groupCode = "I3";

        //check if these variables are missing from this year's dataset
        $graph->mainVarUnavailable = !$graph->ds->isVariableInData($mainVarCode);
        if($groupCode != null)
            $graph->groupingVarUnavailable = !$graph->ds->isVariableInData($groupCode);
        if($graph->mainVarUnavailable || $graph->groupingVarUnavailable)
            return $graph;

        //Get variables
        $graph->mainVariable = $graph->ds->getCutoffVariable($mainVarCode);
        $graph->groupingVariable = $graph->ds->getVariable($groupCode);
        //$graph->title = '"'.$graph->mainVariable->summary.'" by Number of Assets';

        //Create the data structure used by AmCharts for line graphs
        //[['answer' => 0, 'v0' => Variable0 percent, 'v1' => Variable1 percent, ...], ['answer' => 1, ...]]
        //where 'answer' is number 3TS assets
        for ($assetNum = 0; $assetNum <= 6; $assetNum++)
        {
            $assetData = ["answer" => $assetNum];
            $filter = $graph->ds->createFilterString(null, null, null, null, $pyramid, null, $assetNum+1); //add 1 b/c answer1 = 0, answer2 = 1

            $graph->mainVariable->initializeCounts($graph->groupingVariable);
            $graph->ds->getCutoffPositives($graph->mainVariable, $graph->groupingVariable, $filter);
            $graph->ds->getCutoffTotal($graph->mainVariable, $graph->groupingVariable, $filter);

            for ($i = 0; $i < count($graph->mainVariable->counts); $i++)
            {
                //If any of the (variable x asset) totals are <= 10, suppress the value and indicate that the graph has suppression
                if($graph->mainVariable->getTotal($i + 1) <= 10) {
                    $assetData['v'.$i] = null;
                    $graph->hasSuppression = true;
                }
                else
                    $assetData['v'.$i] = round($graph->mainVariable->getPercent($i + 1) * 100, 1);
            }

            //non-binary doesn't exist in <2022 surveys
            if($groupCode == "gender_nb" && $year < 2022)
                $assetData['v2'] = null;

            $graph->percentData[] = $assetData;
            $graph->assetTotals[] = count($graph->mainVariable->totals) === 0 ? null : array_sum($graph->mainVariable->totals);
        }

        //If the graph was grouped, there's a line (label) for each group var answer option. Otherwise, there's one line
        $graph->labels = $groupCode ? $graph->groupingVariable->labels : [$graph->mainVariable->cutoff_summary];
        $graph->notes = VariableNotes::getNotes($mainVarCode);
        $graph->graphHeight = 700;
        return $graph;
    }

    /**
     *
     * @param int|null $gradeFilter
     * @param int|null $genderFilter
     * @param int|null $raceFilter
     * @param int|null $sexOrientationFilter
     * @param int|null $pyramidFilter
     * @param int|null $raceSimplifiedFilter
     * @param int|null $numAssetsFilter
     * @param int|null $transgenderFilter
     * @param int|null $disabilityFilter
     * @return string
     */
    private function addFilter(?int $gradeFilter, ?int $genderFilter, ?int $raceFilter, ?int $sexOrientationFilter, ?int $pyramidFilter, ?int $raceSimplifiedFilter,
                               ?int $numAssetsFilter, ?int $transgenderFilter, ?int $disabilityFilter) : string
    {
        $this->gradeFilter = $gradeFilter;
        $this->genderFilter = $genderFilter;
        $this->raceFilter = $raceFilter;
        $this->sexOrientationFilter = $sexOrientationFilter;
        $this->pyramidFilter = $pyramidFilter;
        $this->raceSimplifiedFilter = $raceSimplifiedFilter;
        $this->numAssetsFilter = $numAssetsFilter;
        $this->transgenderFilter = $transgenderFilter;
        $this->disabilityFilter = $disabilityFilter;
        return $this->ds->createFilterString($gradeFilter, $genderFilter, $raceFilter, $sexOrientationFilter, $pyramidFilter, $raceSimplifiedFilter,
            $numAssetsFilter, $transgenderFilter, $disabilityFilter);
    }
}