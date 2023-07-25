<?php
/**
 * Provide service function to access data from database
 */
require_once 'ConnectionManager.php';
require_once 'CutoffVariable.php';
require_once 'MultiVariable.php';

class DataService {

    public mysqli|null|false $connection;
    protected static DataService $instance;
    const EIGHT_TO_TWELVE = '8to12';
    const SIXTH = '6th';
    private string $datatable;
    private string $variable_table;
    private bool $is8to12;
    private int $year;

    protected function __construct ()
    {
        $cm = new ConnectionManager();
        $this->connection = mysqli_connect($cm->server, $cm->username, $cm->password, $cm->databasename, $cm->port);
        $this->connection->set_charset('utf8');
        $this->throwExceptionOnError();
    }

    /** @param $year int
     *  @param $grade string
     *  @return DataService */
    public static function getInstance(int $year, string $grade): DataService
    {
        if(!isset(DataService::$instance))
            DataService::$instance = new DataService();
        DataService::$instance->datatable = 'data_'.$year.'_'.$grade;
        DataService::$instance->variable_table = 'variables_'.$grade;
        DataService::$instance->is8to12 = $grade == self::EIGHT_TO_TWELVE;
        DataService::$instance->year = $year;
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
        if($labels != null) {
            for ($i = 0; $i < count($labels); $i++) {
                $label = $labels[$i];
                if ($label != null && $label != '')
                    $variable->labels[] = $label;
            }
        }

        return $variable;
    }

    /**
     * Get all variables for the currently selected dataset and year
     * @return array
     * @throws Exception
     */
    public function getVariables() : array
    {

        $result = $this->query("SELECT v.code, v.question, v.summary, v.category FROM $this->variable_table v 
            JOIN variable_year y ON v.code=y.code WHERE y.year=$this->year AND y.dataset_8to12=? ORDER BY v.display_order", [($this->is8to12) ? 1 : 0]);
        return $this->fetchAllObjects($result, MultiVariable::class);
    }

    /**@return CutoffVariable[]     */
    public function getTrendVariables()
    {
        $result = $this->query("SELECT v.code, v.question, v.cutoff_summary, v.category, v.low_cutoff, v.high_cutoff, v.total_cutoff 
          FROM $this->variable_table v JOIN variable_year y ON v.code=y.code 
            WHERE v.has_trends=1 AND y.year=$this->year AND y.dataset_8to12=? ORDER BY v.display_order", [($this->is8to12) ? 1 : 0]);
        return $this->fetchAllObjects($result, CutoffVariable::class);
    }

    /**@return CutoffVariable[]     */
    public function get3TSVariables()
    {
        $result = $this->query("SELECT v.code, v.question, v.cutoff_summary, v.category, v.low_cutoff, v.high_cutoff, v.total_cutoff 
          FROM $this->variable_table v JOIN variable_year y ON v.code=y.code 
            WHERE v.has_trends=1 AND y.year=$this->year AND y.dataset_8to12=? AND v.code NOT IN ('PF9', 'C2', 'LS4', 'C10', 'PS3', 'PC2') ORDER BY v.display_order", [($this->is8to12) ? 1 : 0]);
        return $this->fetchAllObjects($result, CutoffVariable::class);
    }

    /**
     * Was this variable collected this survey year?
     * @param $code string
     * @return bool
     */
    public function isVariableInData($code) {
        $result = $this->query("SHOW COLUMNS FROM $this->datatable  LIKE '?'",[$code]);
        if($result->fetch_row())
            return true;
        return false;
    }

    public function isUnweighted($code) {
        return in_array($code, ['I1','I2','I3','gender_c','I3A','I4','race_eth','race','I7','I7A','language','X9','Pyramid_Code']);
    }

    public function isIdentifying($code) {
        return in_array($code, ['I1','I2','I3','gender_c','I3A','I4','race_eth','race','I7','I7A','language','I8','I9','X9','RS1','RS2','RC17','B3','M4','SHD7','Pyramid_Code']);
    }

    /**
     * @param $mainVar MultiVariable
     * @param MultiVariable|null $groupVar MultiVariable
     * @return bool
     */
    public function checkAnonymityThreshold(MultiVariable $mainVar, ?MultiVariable $groupVar) : bool {
        $threshold = 10;

        if($this->isIdentifying($mainVar->code) && $this->isIdentifying($groupVar?->code)) {
            //each value must be over threshold
            foreach ($mainVar->counts as $count_group) {
                foreach ($count_group as $count) {
                    if ($count < $threshold && $count > 0)
                        return true;
                }
            }
        }
        else if($this->isIdentifying($mainVar->code)) {
            //each main Total must be over threshold (main total = sum of values in count group)
            foreach ($mainVar->counts as $count_group) {
                $mainTotal = 0;
                foreach ($count_group as $count) {
                    $mainTotal += $count;
                }
                if ($mainTotal < $threshold && $mainTotal > 0)
                    return true;
            }
        }
        else if($this->isIdentifying($groupVar?->code)) {
            //each group Total must be over threshold, this is already calculated
            foreach ($mainVar->totals as $total) {
                if ($total < $threshold && $total > 0)
                    return true;
            }
        }
        else {
            //overall total must be over threshold
            $overallTotal = 0;
            foreach ($mainVar->totals as $total) {
                $overallTotal += $total;
            }
            if ($overallTotal < $threshold && $overallTotal > 0)
                return true;
        }

        return false;
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

        //don't use weighting for demographics questions
        if($this->isUnweighted($mainVar->code))
            $counter = "COUNT(1)";
        else
            $counter = "COALESCE(SUM(wgt),0)";

        if ($groupVar != null) {
            $groupcode = $groupVar->code;
            $stmt = $this->connection->query("SELECT $counter as num, $varcode as answer, $groupcode as subgroup 
            FROM $this->datatable 
            WHERE $varcode IS NOT NULL AND $groupcode IS NOT NULL AND $filter 
            GROUP BY $varcode, $groupcode");
        } else {
            $stmt = $this->connection->query("SELECT $counter as num, $varcode as answer 
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

        //don't use weighting for demographics questions
        if($this->isUnweighted($mainVar->code))
            $counter = "COUNT(1)";
        else
            $counter = "COALESCE(SUM(wgt),0)";

        if($groupVar != null)
        {
            $groupcode = $groupVar->code;
            $stmt = $this->connection->query("SELECT $counter as num, $groupcode as subgroup 
                FROM $this->datatable 
                WHERE $groupcode IS NOT NULL AND $filter AND $varcode IS NOT NULL 
                GROUP BY $groupcode");
        }
        else {
            $stmt = $this->connection->query("SELECT $counter as num 
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
        //don't use weighting for demographics questions
        if($this->isUnweighted($mainVar->code))
            $counter = "COUNT(1)";
        else
            $counter = "COALESCE(SUM(wgt),0)";

        $varcode = $mainVar->code;

        if($groupVar != null)
        {
            $groupcode = $groupVar->code;
            $stmt = $this->connection->query("SELECT $counter as num FROM $this->datatable 
                WHERE ($varcode IS NULL OR $groupcode IS NULL) AND $filter");
        }
        else {
            $stmt = $this->connection->query("SELECT $counter as num FROM $this->datatable 
                WHERE ($varcode IS NULL) AND $filter");
        }
        $this->throwExceptionOnError();

        return $stmt->fetch_row()[0];
    }

    public function createFilterString($grade, $gender, $race, $sexual_orientation, $pyramid, $race_simplified = null, $num_assets = null) {
        $filter = " 1 ";
        if ($grade != null)
            $filter .= " AND I2 = ".$this->connection->real_escape_string($grade);
        if ($gender != null)
            $filter .= " AND I3 = ".$this->connection->real_escape_string($gender);
        if ($race != null)
            $filter .= " AND race_eth = ".$this->connection->real_escape_string($race);
        if ($sexual_orientation != null)
            $filter .= " AND X9 = ".$this->connection->real_escape_string($sexual_orientation);
        if ($pyramid != null && $pyramid != '')
            $filter .= " AND Pyramid_Code = ".$this->connection->real_escape_string($pyramid);
        if ($race_simplified != null)
            $filter .= " AND race = ".$this->connection->real_escape_string($race_simplified);
        if ($num_assets !== null)
            $filter .= " AND assets_3TS = ".$this->connection->real_escape_string($num_assets);
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
     * @param $class string
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