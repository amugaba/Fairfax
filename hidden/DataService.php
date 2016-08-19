<?php
/**
 * Provide service function to access data from database
 */
require_once 'ConnectionManager.php';
require_once 'VariableVO.php';

class DataService {

    public $connection;
    public $vartable = "variables_2015";
    public $datatable = "data_2015_merged";

    public function __construct ()
    {
        $cm = new ConnectionManager();
        $this->connection = mysqli_connect($cm->server, $cm->username, $cm->password, $cm->databasename, $cm->port);
        $this->throwExceptionOnError($this->connection);
    }

    /**
     * Get all variables
     * @return VariableVO[]
     */
    public function getVariables()
    {
        $stmt = $this->connection->prepare("SELECT autoid, code, question, summary, category FROM $this->vartable");
        $this->throwExceptionOnError();

        $stmt->execute();
        $this->throwExceptionOnError();

        $vars = [];
        $var = new VariableVO();
        $stmt->bind_result($var->autoid, $var->code, $var->question, $var->summary, $var->category);

        while ($stmt->fetch())
        {
            $vars[] = $var;
            $var = new VariableVO();
            $stmt->bind_result($var->autoid, $var->code, $var->question, $var->summary, $var->category);
        }

        return $vars;
    }

    /**
     * Get answer labels of one questions
     * @param int $varid
     * @return string[]
     */
    public function getLabels($varcode)
    {
        $varcode = $this->connection->escape_string($varcode);

        $stmt = $this->connection->query("SELECT answer1,answer2,answer3,answer4,answer5,answer6,answer7,answer8,answer9,
            answer10,answer11,answer12,answer13,answer14,answer15,answer16,answer17,answer18,answer19,answer20,answer21,
            answer22,answer23,answer24,answer25,answer26 FROM $this->vartable WHERE code='$varcode'");
        $this->throwExceptionOnError();

        $labels = $stmt->fetch_row();

        //remove empty labels
        $newlabels = [];
        for($i=0; $i<count($labels); $i++)
        {
            $label = $labels[$i];
            if($label != null && $label != '')
                $newlabels[] = $label;
        }
        return $newlabels;
    }

    /**
     * Get data for one question. Data is returned as array of associative arrays: [['answer' => key1, 'num' => value1], ['answer' => key2, 'num' => value2], ...]
     * @param string $varcode
     * @param string $groupcode
     * @return array
     */
    public function getData($varcode, $groupcode, $filter)
    {
        $varcode = $this->connection->escape_string($varcode);
        $groupcode = $this->connection->escape_string($groupcode);

        if($groupcode != 'none') {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $varcode as answer, $groupcode as subgroup FROM $this->datatable WHERE $groupcode IS NOT NULL AND $filter GROUP BY $varcode, $groupcode");
        }
        else {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $varcode as answer FROM $this->datatable WHERE $filter GROUP BY $varcode");
        }
        $this->throwExceptionOnError();

        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    public function converToPercents($data, $totals, $doGrouping)
    {
        $newdata = [];
        for($i=0; $i<count($data); $i++) {
            if($doGrouping)
                $subgroup = intval($data[$i]['subgroup'])-1;
            else
                $subgroup = 0;

            $newdata[$i] = $data[$i];
            $newdata[$i]['num'] /= $totals[$subgroup]['num'];
        }
        return $newdata;
    }

    /**
     * @param $data
     * @param $answer
     * @param $group
     * @return float
     */
    public function findData($data, $answer, $group, $isGrouped)
    {
        foreach($data as $row) {
            if($row['answer'] == $answer && (!$isGrouped || $row['subgroup'] == $group))
                return $row['num'];
        }
    }


    /**
     * Get data for one question. Data is returned as array of associative arrays: [['answer' => key1, 'num' => value1], ['answer' => key2, 'num' => value2], ...]
     * @param string $groupcode
     * @return array
     */
    public function getGroupTotals($groupcode, $filter)
    {
        $groupcode = $this->connection->escape_string($groupcode);

        if($groupcode != 'none')
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $groupcode as subgroup FROM $this->datatable WHERE $groupcode IS NOT NULL AND $filter GROUP BY $groupcode");
        else
            $stmt = $this->connection->query("SELECT SUM(wgt) as num FROM $this->datatable WHERE $filter");
        $this->throwExceptionOnError();

        $totals = $stmt->fetch_all(MYSQLI_ASSOC);
        return $totals;
    }

    /**
     * Get the number of cases between the cutoff points for the given question
     * Data is returned as array of associative arrays: [['cutoff' => null, 'num' => value1], ['answer' => 0, 'num' => value2], ['answer' => 1, 'num' => value3]]
     * The answer=1 is the number of cases in the cutoff range.
     * @param string $varcode
     * @param string $groupcode
     * @param int $low
     * @param int $high
     * @return int
     */
    public function getDataCutoff($varcode, $groupcode, $low, $high)
    {
        $varcode = $this->connection->escape_string($varcode);
        $groupcode = $this->connection->escape_string($groupcode);

        $query = "SELECT SUM(wgt) as num";
        if($groupcode != 'none') {
            $query .= ", $groupcode as subgroup";
        }

        $query .= " FROM $this->datatable WHERE 1";

        if($low != null) {
            $query .= " AND $varcode >= $low";
        }
        if($high != null) {
            $query .= " AND $varcode <= $high";
        }
        if($groupcode != 'none') {
            $query .= " AND $groupcode IS NOT NULL GROUP BY $groupcode";
        }

        /*if($groupcode != 'none') {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $groupcode as subgroup FROM data WHERE $varcode >= $low AND $varcode <= $high AND $groupcode IS NOT NULL GROUP BY $groupcode");
        }
        else {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num FROM data WHERE $varcode >= $low AND $varcode <= $high");
        }*/
        $stmt = $this->connection->query($query);
        $this->throwExceptionOnError();

        $results = $stmt->fetch_all(MYSQLI_ASSOC);

        if($groupcode != 'none') {
            //get group values
            $groupAnswers = $this->getLabels($groupcode);

            //check if any group values are missing from data
            for ($i = 0; $i < count($groupAnswers); $i++) {
                if($results[$i]['subgroup'] != $i+1) {
                    array_splice($results,$i,0, array(array('num' => "0", 'subgroup' => "".($i+1))));
                }
            }
        }

        return $results;
    }

    public function getGroupTotalsCutoff($varcode, $groupcode, $totalCutoff)
    {
        $groupcode = $this->connection->escape_string($groupcode);

        $query = "SELECT SUM(wgt) as num";
        if($groupcode != 'none') {
            $query .= ", $groupcode as subgroup";
        }

        $query .= " FROM $this->datatable WHERE $varcode IS NOT NULL";

        if($totalCutoff != null) {
            $query .= " AND $varcode >= $totalCutoff";
        }
        if($groupcode != 'none') {
            $query .= " AND $groupcode IS NOT NULL GROUP BY $groupcode";
        }

        /*if($groupcode != 'none')
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $groupcode as subgroup FROM data WHERE $varcode > $totalCutoff AND $groupcode IS NOT NULL GROUP BY $groupcode");
        else
            $stmt = $this->connection->query("SELECT SUM(wgt) as num FROM data WHERE $varcode > $totalCutoff");
        */
        $stmt = $this->connection->query($query);
        $this->throwExceptionOnError();

        $results = $stmt->fetch_all(MYSQLI_ASSOC);

        if($groupcode != 'none') {
            //get group values
            $groupAnswers = $this->getLabels($groupcode);

            //check if any group values are missing from data
            for ($i = 0; $i < count($groupAnswers); $i++) {
                if($results[$i]['subgroup'] != $i+1) {
                    array_splice($results,$i,0, array(array('num' => "0", 'subgroup' => "".($i+1))));
                }
            }
        }

        return $results;
    }

    /**
     * Utitity function to throw an exception if an error occurs
     * while running a mysql command.
     */
    private function throwExceptionOnError ($link = null)
    {
        if ($link == null) {
            $link = $this->connection;
        }

        if (mysqli_error($link)) {
            $msg = mysqli_errno($link) . ": " . mysqli_error($link);
            throw new Exception('MySQL Error - ' . $msg);
        }
    }
}