<?php
/**
 * Provide service function to access data from database
 */
require_once 'ConnectionManager.php';
require_once 'Variable.php';
require_once 'Answer.php';

class DataService {

    public $connection;
    public $vartable = "variables_2015";
    public $datatable = "data_2015_8to12";

    public function __construct ()
    {
        $cm = new ConnectionManager();
        $this->connection = mysqli_connect($cm->server, $cm->username, $cm->password, $cm->databasename, $cm->port);
        $this->throwExceptionOnError($this->connection);
    }

    /**
     * Get variable by code
     * @param string $code
     * @return Variable
     */
    public function getVariableByCode($code)
    {
        $stmt = $this->connection->prepare("SELECT autoid, code, question, summary, category FROM $this->vartable WHERE code=?");
        $this->throwExceptionOnError();
        $stmt->bind_param('s',$code);
        $this->throwExceptionOnError();
        $stmt->execute();
        $this->throwExceptionOnError();

        $var = new Variable();
        $stmt->bind_result($var->autoid, $var->code, $var->question, $var->summary, $var->category);
        if(!$stmt->fetch()){
            return null;
        }
        $stmt->free_result();

        //Get Answers to the Question
        $stmt = $this->connection->prepare("SELECT answer1,answer2,answer3,answer4,answer5,answer6,answer7,answer8,answer9,
            answer10,answer11,answer12,answer13,answer14,answer15,answer16,answer17,answer18,answer19,answer20,answer21,
            answer22,answer23,answer24,answer25,answer26 FROM $this->vartable WHERE code=?");
        $this->throwExceptionOnError();
        $stmt->bind_param('s',$code);
        $this->throwExceptionOnError();
        $stmt->execute();
        $this->throwExceptionOnError();

        $result = $stmt->get_result();
        $labels = $result->fetch_row();

        //add Answers to Question
        for($i=0; $i<count($labels); $i++)
        {
            $label = $labels[$i];
            if($label != null && $label != '')
                $var->addAnswer($i+1,$label);
        }
        return $var;
    }

    /**
     * Get all variables
     * @return Variable[]
     */
    public function getVariables()
    {
        $stmt = $this->connection->prepare("SELECT autoid, code, question, summary, category FROM $this->vartable");
        $this->throwExceptionOnError();

        $stmt->execute();
        $this->throwExceptionOnError();

        $vars = [];
        $var = new Variable();
        $stmt->bind_result($var->autoid, $var->code, $var->question, $var->summary, $var->category);

        while ($stmt->fetch())
        {
            $vars[] = $var;
            $var = new Variable();
            $stmt->bind_result($var->autoid, $var->code, $var->question, $var->summary, $var->category);
        }

        return $vars;
    }

    /**
     * Get the weighted count of students that chose each answer for the given question.     *
     * @param Variable $mainVar
     * @param Variable $groupVar
     * @param string $filter
     * @return array
     */
    public function getData($mainVar, $groupVar, $filter)
    {
        $varcode = $mainVar->code;

        if($groupVar != null)
        {
            $groupcode = $groupVar->code;
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num, $varcode as answer, $groupcode as subgroup 
                FROM $this->datatable 
                WHERE $varcode IS NOT NULL AND $groupcode IS NOT NULL AND $filter 
                GROUP BY $varcode, $groupcode");
        }
        else {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num, $varcode as answer 
                FROM $this->datatable 
                WHERE $varcode IS NOT NULL AND $filter 
                GROUP BY $varcode");
        }
        $this->throwExceptionOnError();

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
            $answer = $mainVar->getAnswer($row['answer']);
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            $answer->addCount($subgroup, $row['num']);
        }

        return $mainVar;
    }

    /**
     * Get the total number of students that answered the given question (non-null response).
     * @param Variable $mainVar
     * @param Variable $groupVar
     * @param string $filter
     * @return array
     */
    public function getGroupTotals($mainVar, $groupVar, $filter)
    {
        $varcode = $mainVar->code;

        if($groupVar != null)
        {
            $groupcode = $groupVar->code;
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num, $groupcode as subgroup 
                FROM $this->datatable 
                WHERE $groupcode IS NOT NULL AND $filter AND $varcode IS NOT NULL 
                GROUP BY $groupcode");
        }
        else {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num 
                FROM $this->datatable 
                WHERE $filter AND $varcode IS NOT NULL");
        }
        $this->throwExceptionOnError();

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            $mainVar->addTotal($subgroup, $row['num']);
        }

        return $mainVar;
    }

    /**
     * Get the number of students that selected an answer within the cutoff points.
     * @param Answer $answer
     * @param Variable $groupVar
     */
    public function getDataCutoff($answer, $groupVar)
    {
        $cutoffQuery = "1";
        if($answer->lowCutoff != null) {
            $cutoffQuery .= " AND $answer->code >= $answer->lowCutoff";
        }
        if($answer->highCutoff != null) {
            $cutoffQuery .= " AND $answer->code <= $answer->highCutoff";
        }

        if($groupVar != null) {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num, $groupVar->code as subgroup
                FROM $this->datatable 
                WHERE $groupVar->code IS NOT NULL AND $cutoffQuery
                GROUP BY $groupVar->code");
        }
        else {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num
                FROM $this->datatable 
                WHERE $cutoffQuery");
        }
        $this->throwExceptionOnError();

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            $answer->addCount($subgroup, $row['num']);
        }
    }

    /**
     * Get the total number of students within the cutoff points. Then calculate the percentage using the count and total values.
     * @param Answer $answer
     * @param Variable $groupVar
     */
    public function getGroupTotalsCutoff($answer, $groupVar)
    {
        $cutoffQuery = "1";
        if($answer->totalCutoff != null) {
            $cutoffQuery .= " AND $answer->code >= $answer->totalCutoff";
        }

        if($groupVar != null) {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num, $groupVar->code as subgroup
                FROM $this->datatable 
                WHERE $answer->code IS NOT NULL AND $groupVar->code IS NOT NULL AND $cutoffQuery
                GROUP BY $groupVar->code");
        }
        else {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num
                FROM $this->datatable 
                WHERE $answer->code IS NOT NULL AND $cutoffQuery");
        }
        $this->throwExceptionOnError();

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            $answer->addTotal($subgroup, $row['num']);
        }
    }

    public function getNoResponseCount($varcode, $groupcode)
    {
        $varcode = $this->connection->escape_string($varcode);
        $groupcode = $this->connection->escape_string($groupcode);

        $query = "SELECT SUM(wgt) as num FROM $this->datatable WHERE $varcode IS NULL";
        if($groupcode != 'none') {
            $query .= " OR $groupcode IS NULL";
        }

        $stmt = $this->connection->query($query);
        $this->throwExceptionOnError();

        return $stmt->fetch_row()[0];
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