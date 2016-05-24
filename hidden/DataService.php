<?php
/**
 * Provide service function to access data from database
 */
require_once 'ConnectionManager.php';
require_once 'VariableVO.php';

class DataService {

    public $connection;

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
        $stmt = $this->connection->prepare("SELECT autoid, code, question, summary, category FROM variables");
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
            answer22,answer23,answer24,answer25,answer26 FROM variables WHERE code='$varcode'");
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
    public function getData($varcode, $groupcode)
    {
        $varcode = $this->connection->escape_string($varcode);
        $groupcode = $this->connection->escape_string($groupcode);

        if($groupcode != 'none') {
            //$stmt = $this->connection->query("SELECT SUM(wgt) as num, $varcode as answer, $groupcode as subgroup FROM data WHERE $groupcode IS NOT NULL GROUP BY $varcode, $groupcode");
            //$stmt2 = $this->connection->query("SELECT SUM(wgt) as num, $groupcode as subgroup FROM data WHERE $groupcode IS NOT NULL GROUP BY $groupcode");
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $varcode as answer, $groupcode as subgroup FROM data WHERE $groupcode IS NOT NULL GROUP BY $varcode, $groupcode");
        }
        else {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $varcode as answer FROM data GROUP BY $varcode");
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
    public function getGroupTotals($groupcode)
    {
        $groupcode = $this->connection->escape_string($groupcode);

        if($groupcode != 'none')
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $groupcode as subgroup FROM data WHERE $groupcode IS NOT NULL GROUP BY $groupcode");
        else
            $stmt = $this->connection->query("SELECT SUM(wgt) as num FROM data");
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

        if($groupcode != 'none') {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $groupcode as subgroup FROM data WHERE $varcode >= $low AND $varcode <= $high AND $groupcode IS NOT NULL GROUP BY $groupcode");
        }
        else {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num FROM data WHERE $varcode >= $low AND $varcode <= $high");
        }

        /*if($grouping == 'grade') {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $groupcode as grade FROM data WHERE $varcode >= $low AND $varcode <= $high AND $groupcode IS NOT NULL GROUP BY $groupcode");
            $stmt2 = $this->connection->query("SELECT SUM(wgt) as num, I2 as grade FROM data WHERE $varcode > $totalCutoff GROUP BY I2");
        }
        else if($grouping == 'gender') {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, I3 as gender FROM data WHERE $varcode >= $low AND $varcode <= $high GROUP BY I3");
            $stmt2 = $this->connection->query("SELECT SUM(wgt) as num, I3 as gender FROM data WHERE $varcode > $totalCutoff GROUP BY I3");
        }
        else if($grouping == 'race') {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, race FROM data WHERE $varcode >= $low AND $varcode <= $high GROUP BY race");
            $stmt2 = $this->connection->query("SELECT SUM(wgt) as num, race FROM data WHERE $varcode > $totalCutoff GROUP BY race");
        }
        else {
            $stmt = $this->connection->query("SELECT SUM(wgt) as num FROM data WHERE $varcode >= $low AND $varcode <= $high");
            $stmt2 = $this->connection->query("SELECT SUM(wgt) as num FROM data WHERE $varcode > $totalCutoff");
        }*/
        $this->throwExceptionOnError();

        /*$data = $stmt->fetch_all(MYSQLI_ASSOC);
        $totals = $stmt2->fetch_all(MYSQLI_ASSOC);

        //percentage = num/total
        for($i=0;$i<count($data);$i++) {
            $data[$i]['num'] /= $totals[$i]['num'];
        }

        return $data;*/
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    public function getGroupTotalsCutoff($varcode, $groupcode, $totalCutoff)
    {
        $groupcode = $this->connection->escape_string($groupcode);

        if($groupcode != 'none')
            $stmt = $this->connection->query("SELECT SUM(wgt) as num, $groupcode as subgroup FROM data WHERE $varcode > $totalCutoff AND $groupcode IS NOT NULL GROUP BY $groupcode");
        else
            $stmt = $this->connection->query("SELECT SUM(wgt) as num FROM data WHERE $varcode > $totalCutoff");
        $this->throwExceptionOnError();

        $totals = $stmt->fetch_all(MYSQLI_ASSOC);
        return $totals;
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