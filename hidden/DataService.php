<?php
/**
 * Provide service function to access data from database
 */
require_once 'ConnectionManager.php';
require_once 'CutoffVariable.php';
require_once 'MultiVariable.php';

class DataService {

    public $connection;
    protected static $instance = null;
    const EIGHT_TO_TWELVE = '8to12';
    const SIXTH = '6th';
    private $datatable;
    private $variable_table;

    protected function __construct ()
    {
        $cm = new ConnectionManager();
        $this->connection = mysqli_connect($cm->server, $cm->username, $cm->password, $cm->databasename, $cm->port);
        $this->throwExceptionOnError();
    }

    /** @param $year int
     *  @param $grade string
     *  @return DataService */
    public static function getInstance($year, $grade) {
        if(DataService::$instance === null)
            DataService::$instance = new DataService();
        DataService::$instance->datatable = 'data_'.$year.'_'.$grade;
        DataService::$instance->variable_table = 'variables_'.$grade;
        return DataService::$instance;
    }

    /**@param string $code
     * @return CutoffVariable     */
    public function getCutoffVariable($code)
    {
        if($code == null)
            return null;

        $result = $this->query("SELECT autoid, code, question, cutoff_summary, cutoff_tooltip, category, low_cutoff, high_cutoff, total_cutoff 
            FROM $this->variable_table WHERE code='?'", [$code]);
        return $this->fetchObject($result, CutoffVariable::class);
    }

    /**@param string $code
     * @return MultiVariable     */
    public function getMultiVariable($code)
    {
        if($code == null)
            return null;

        $result = $this->query("SELECT autoid, code, question, summary, category FROM $this->variable_table WHERE code='?'", [$code]);
        $variable = $this->fetchObject($result, MultiVariable::class);

        //Get Answers to the Question
        $result = $this->query("SELECT answer1,answer2,answer3,answer4,answer5,answer6,answer7,answer8,answer9,
        answer10,answer11,answer12,answer13,answer14,answer15,answer16,answer17,answer18,answer19,answer20,answer21,
        answer22,answer23,answer24,answer25,answer26 FROM $this->variable_table WHERE code='?'", [$code]);

        $labels = $result->fetch_row();

        //add answer labels to Question
        for ($i = 0; $i < count($labels); $i++) {
            $label = $labels[$i];
            if ($label != null && $label != '')
                $variable->labels[] = $label;
        }

        return $variable;
    }

    /**Get all variables
     * @return MultiVariable[]     */
    public function getVariables()
    {
        $result = $this->query("SELECT autoid, code, question, summary, category FROM $this->variable_table");
        return $this->fetchAllObjects($result, MultiVariable::class);
    }

    /**@return CutoffVariable[]     */
    public function getTrendVariables()
    {
        $result = $this->query("SELECT autoid, code, question, cutoff_summary, category, low_cutoff, high_cutoff, total_cutoff 
          FROM $this->variable_table WHERE has_trends=1");
        return $this->fetchAllObjects($result, CutoffVariable::class);
    }

    /**
     * Get the weighted count of students that chose each answer for the given question.     *
     * @param MultiVariable $mainVar
     * @param MultiVariable $groupVar
     * @param string $filter
     */
    public function getMultiPositives($mainVar, $groupVar, $filter)
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
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            $mainVar->addCount($row['answer'], $subgroup, $row['num']);
        }
    }

    /**
     * Get the total number of students that answered the given question (non-null response).
     * @param MultiVariable $mainVar
     * @param MultiVariable $groupVar
     * @param string $filter
     */
    public function getMultiTotals($mainVar, $groupVar, $filter)
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
    }

    /**Get the number of students that selected an answer within the cutoff points.
     * @param CutoffVariable $variable
     * @param Variable $groupVar
     * @param string $filter    */
    public function getCutoffPositives($variable, $groupVar, $filter)
    {
        $cutoffQuery = "1";
        if($variable->lowCutoff != null) {
            $cutoffQuery .= " AND $variable->code >= $variable->lowCutoff";
        }
        if($variable->highCutoff != null) {
            $cutoffQuery .= " AND $variable->code <= $variable->highCutoff";
        }

        if($groupVar != null) {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num, $groupVar->code as subgroup
                FROM $this->datatable 
                WHERE $groupVar->code IS NOT NULL AND $cutoffQuery AND $filter
                GROUP BY $groupVar->code");
        }
        else {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num
                FROM $this->datatable 
                WHERE $cutoffQuery AND $filter");
        }
        $this->throwExceptionOnError();

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            $variable->addCount($subgroup, $row['num']);
        }
    }

    /**Get the total number of students, subject to the total cutoff.
     * @param CutoffVariable $variable
     * @param Variable $groupVar
     * @param string $filter    */
    public function getCutoffTotal($variable, $groupVar, $filter)
    {
        $cutoffQuery = "1";
        if($variable->totalCutoff != null) {
            $cutoffQuery .= " AND $variable->code >= $variable->totalCutoff";
        }

        if($groupVar != null) {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num, $groupVar->code as subgroup
                FROM $this->datatable 
                WHERE $variable->code IS NOT NULL AND $groupVar->code IS NOT NULL AND $cutoffQuery AND $filter
                GROUP BY $groupVar->code");
        }
        else {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num
                FROM $this->datatable 
                WHERE $variable->code IS NOT NULL AND $cutoffQuery AND $filter");
        }
        $this->throwExceptionOnError();

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            $variable->addTotal($subgroup, $row['num']);
        }
    }

    /**
     * Get the total number of students that did not answer one of the questions (null response).
     * @param MultiVariable $mainVar
     * @param MultiVariable $groupVar
     * @param string $filter
     */
    public function getNoResponseCount($mainVar, $groupVar, $filter)
    {
        $varcode = $mainVar->code;

        if($groupVar != null)
        {
            $groupcode = $groupVar->code;
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num FROM $this->datatable 
                WHERE ($varcode IS NULL OR $groupcode IS NULL) AND $filter");
        }
        else {
            $stmt = $this->connection->query("SELECT COALESCE(SUM(wgt),0) as num FROM $this->datatable 
                WHERE ($varcode IS NULL) AND $filter");
        }
        $this->throwExceptionOnError();

        return $stmt->fetch_row()[0];
    }

    public function createFilterString($grade, $gender, $race) {
        $filter = " 1 ";
        if ($grade != null)
            $filter .= " AND I2 = ".$this->connection->real_escape_string($grade);
        if ($gender != null)
            $filter .= " AND I3 = ".$this->connection->real_escape_string($gender);
        if ($race != null)
            $filter .= " AND race_eth = ".$this->connection->real_escape_string($race);
        return $filter;
    }

    /**Run mysql query after escaping input
     * @param $stmt string
     * @param $params array
     * @return bool|mysqli_result
     * @throws Exception     */
    private function query($stmt, $params = null) {
        if($params != null) {
            for($i=0; $i<count($params); $i++) {
                $val = $params[$i];
                if($val === null)
                    throw new Exception("Query: $stmt Missing paramater ".($i+1));
                if($val === true)
                    $val = 1;
                if($val === false)
                    $val = 0;
                $params[$i] = $this->connection->real_escape_string($val);
            }

            $index = strpos($stmt, '?');
            $i = 0;
            while ($index) {
                $stmt = substr($stmt, 0, $index) . $params[$i] . substr($stmt, $index + 1);
                $index = strpos($stmt, '?');
                $i++;
            }
        }

        $result = $this->connection->query($stmt);
        $this->throwExceptionOnError();

        return $result;
    }
    /**@param $result mysqli_result
     * @param $class
     * @return array     */
    private function fetchAllObjects($result, $class) {
        $objs = [];
        while($row = $result->fetch_object()) {
            $obj = new $class;
            $obj->fill($row);
            $objs[] = $obj;
        }

        $result->free_result();
        return $objs;
    }
    /**@param $result mysqli_result
     * @param $class ReflectionClass
     * @return mixed|null Returns null if no rows in result set. */
    private function fetchObject($result, $class) {
        if($row = $result->fetch_object()) {
            $obj = new $class;
            $obj->fill($row);
            $result->free_result();
            return $obj;
        }

        $result->free_result();
        return null;
    }
    /** Utility function to throw an exception if an error occurs while running a mysql command.   */
    protected function throwExceptionOnError ()
    {
        $link = $this->connection;
        if (mysqli_error($link)) {
            $msg = mysqli_errno($link) . ": " . mysqli_error($link);
            throw new Exception('MySQL Error - ' . $msg);
        }
    }
}