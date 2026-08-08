<?php
/**
 * Provide service function to access data from database
 */
require_once 'ConnectionManager.php';
require_once 'CutoffVariable.php';
require_once 'MultiVariable.php';
require_once 'Category.php';
require_once 'Graph.php';
require_once 'VariableNotes.php';

class DataService {

    public mysqli|null|false $connection;
    protected static DataService $instance;
    protected string $last_raw_statement;
    protected ?array $last_params;
    protected string $last_full_statement;

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

    /**
     * @param string $code
     * @return CutoffVariable|null
     * @throws Exception
     */
    public function getCutoffVariable(string $code): ?CutoffVariable
    {
        if($code == null)
            return null;

        $result = $this->query("SELECT id, year, code, question, cutoff_summary, cutoff_tooltip, category, low_cutoff, high_cutoff, total_cutoff 
            FROM variables WHERE year=? AND code=? AND is8to12=?",
            [$this->year, $code, $this->is8to12 ? 1 : 0]);
        return $this->fetchObject($result, CutoffVariable::class);
    }

    /**
     * @param ?string $code
     * @return MultiVariable|null
     * @throws Exception
     */
    public function getVariable(?string $code): ?MultiVariable
    {
        if($code == null)
            return null;

        $result = $this->query("SELECT * FROM variables WHERE year=? AND code=? AND is8to12=?",
            [$this->year, $code, $this->is8to12 ? 1 : 0]);
        $variable = $this->fetchObject($result, MultiVariable::class);
        if($variable == null)
            return null;

        //Get Answers to the Question
        $result = $this->query("SELECT answer1,answer2,answer3,answer4,answer5,answer6,answer7,answer8,answer9,
        answer10,answer11,answer12,answer13,answer14,answer15,answer16,answer17,answer18,answer19,answer20,answer21,
        answer22,answer23,answer24,answer25,answer26 FROM variables WHERE id=?", [$variable->id]);

        //add answer labels to Question
        $labels = $result->fetch_row();
        foreach ($labels as $label) {
            if ($label != null && $label != '')
                $variable->labels[] = $label;
        }

        return $variable;
    }

    /**
     * Get all variables for the currently selected year and grade
     * @return array
     * @throws Exception
     */
    public function getVariables() : array
    {
        $result = $this->query("SELECT code, question, summary, category FROM variables
            WHERE year=? AND is8to12=? ORDER BY display_order", [$this->year, $this->is8to12 ? 1 : 0]);
        return $this->fetchAllObjects($result, MultiVariable::class);
    }

    /**
     * Get all variables that have trends/cutoffs for the currently selected year and grade
     * @return CutoffVariable[]
     * @throws Exception
     */
    public function getTrendVariablesByYear() : array
    {
        $result = $this->query("SELECT code, question, cutoff_summary AS 'summary', category FROM variables
            WHERE has_trends=1 AND year=? AND is8to12=? GROUP BY code, display_order ORDER BY display_order", [$this->year, $this->is8to12 ? 1 : 0]);
        return $this->fetchAllObjects($result, CutoffVariable::class);
    }

    /**
     * Get all variables that have trends/cutoffs for the currently selected year and grade
     * @param string $yearRange
     * @return CutoffVariable[]
     * @throws Exception
     */
    public function getTrendVariablesByYearRange(string $yearRange) : array
    {
        $whereYear = ($yearRange == Trend::PRE_2024) ? "year <= 2024" : "year >= 2025";
        $result = $this->query("SELECT code, question, cutoff_summary AS 'summary', category FROM variables
            WHERE has_trends=1 AND is8to12=? AND $whereYear GROUP BY code, display_order ORDER BY display_order", [$this->is8to12 ? 1 : 0]);
        return $this->fetchAllObjects($result, CutoffVariable::class);
    }

    /**
     * Get all variables EXCEPT for the Three to Succeed assets for the currently selected year and grade
     * @return CutoffVariable[]
     * @throws Exception
     */
    public function get3TSVariables(): array
    {
        $result = $this->query("SELECT code, question, cutoff_summary AS 'summary', category FROM variables 
            WHERE has_trends=1 AND year=? AND is8to12=? AND code NOT IN ('PF9', 'C2', 'LS4', 'C10', 'PS3', 'PC2') ORDER BY display_order", [$this->year, ($this->is8to12) ? 1 : 0]);
        return $this->fetchAllObjects($result, CutoffVariable::class);
    }

    /**
     * Get all variables in this trend, one for each year
     * @param string $var_code
     * @return CutoffVariable[]
     * @throws Exception
     */
    public function getVariablesInTrend(string $var_code): array
    {
        $result = $this->query("SELECT id, year, code, question, cutoff_summary, cutoff_tooltip, category, low_cutoff, high_cutoff, total_cutoff 
            FROM variables WHERE code=? AND is8to12=? ORDER BY year",
            [$var_code, $this->is8to12 ? 1 : 0]);
        return $this->fetchAllObjects($result, CutoffVariable::class);
    }

    /**
     * Was this variable collected this survey year and grade?
     * @param $code string
     * @return bool
     * @throws Exception
     */
    public function isVariableInData(string $code): bool
    {
        $result = $this->query("SELECT 1 FROM variables WHERE year=? AND code=? AND is8to12=?",[$this->year, $code, $this->is8to12 ? 1 : 0]);
        if($result->fetch_row())
            return true;
        return false;
    }

    /**
     * Is this variable a demographic variable?
     * @param string $code
     * @return bool
     */
    public function isUnweighted(string $code): bool
    {
        return in_array($code, ['I1','I2','I3','gender_c','I3A','I4','race_eth','race','I7','I7A','language','X9','X9A','Pyramid_Code', 'disability_cat',
            'I10A','I10B','I10C','I10D','I10E','I10F','I10G','I10H','I10I','I11']);
    }

    /**
     * Does this variable need to be hidden at small sample sizes because it's identifying?
     * @param string|null $code
     * @return bool
     */
    public function isIdentifying(?string $code): bool
    {
        if($code == null)
            return false;
        return in_array($code, ['I1','I2','I3','gender_c','I3A','I4','race_eth','race','I7','I7A','language','I8','I9','X9','X9A','RS1','RS2','RC17','B3','M4','SHD7','Pyramid_Code']);
    }

    /**
     * Get the categories that appear in the current year and grade
     * @return Category[]
     * @throws Exception
     */
    public function getCategories(): array
    {
        $result = $this->query("SELECT * FROM categories 
            WHERE year=? AND code IN (SELECT category FROM variables WHERE year=? AND is8to12=? GROUP BY category) 
            ORDER BY display_order", [$this->year, $this->year, $this->is8to12 ? 1 : 0]);
        return $this->fetchAllObjects($result, Category::class);
    }

    /**
     * Get the categories that appear in the current year
     * @param int $year
     * @return Category[]
     * @throws Exception
     */
    public function getTrendCategoriesByYear(int $year) : array
    {
        $result = $this->query("SELECT * FROM categories 
            WHERE year=? AND code IN (SELECT category FROM variables WHERE year=? AND has_trends=1 AND is8to12=? GROUP BY category) 
            ORDER BY display_order", [$year, $year, $this->is8to12 ? 1 : 0]);
        return $this->fetchAllObjects($result, Category::class);
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
     * Get the weighted count of students that chose each answer for the given question.
     * @param MultiVariable $mainVar
     * @param MultiVariable|null $groupVar
     * @param string $filter
     * @throws Exception
     */
    public function getMultiPositives(MultiVariable $mainVar, ?MultiVariable $groupVar, string $filter): void
    {
        $varcode = $mainVar->code;

        //don't use weighting for demographic questions
        if($this->isUnweighted($mainVar->code))
            $counter = "COUNT(1)";
        else
            $counter = "COALESCE(SUM(wgt),0)";

        if ($groupVar != null) {
            $group_code = $groupVar->code;
            $stmt = $this->query("SELECT $counter as num, $varcode as answer, $group_code as subgroup 
            FROM $this->datatable 
            WHERE $varcode IS NOT NULL AND $group_code IS NOT NULL AND $filter 
            GROUP BY $varcode, $group_code");
        } else {
            $stmt = $this->query("SELECT $counter as num, $varcode as answer 
            FROM $this->datatable 
            WHERE $varcode IS NOT NULL AND $filter 
            GROUP BY $varcode");
        }

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            $mainVar->addCount($row['answer'], $subgroup, $row['num']);
        }
    }

    /**
     * Get the total number of students that answered the given question (non-null response).
     * @param MultiVariable $mainVar
     * @param MultiVariable|null $groupVar
     * @param string $filter
     * @throws Exception
     */
    public function getMultiTotals(MultiVariable $mainVar, ?MultiVariable $groupVar, string $filter): void
    {
        $var_code = $mainVar->code;

        //don't use weighting for demographic questions
        if($this->isUnweighted($mainVar->code))
            $counter = "COUNT(1)";
        else
            $counter = "COALESCE(SUM(wgt),0)";

        if($groupVar != null)
        {
            $group_code = $groupVar->code;
            $stmt = $this->query("SELECT $counter as num, $group_code as subgroup 
                FROM $this->datatable 
                WHERE $group_code IS NOT NULL AND $filter AND $var_code IS NOT NULL 
                GROUP BY $group_code");
            /* FLIP CROSSTAB GRAPH - Need to fix non-grouped graphs if doing this (total wrong)
            $stmt = $this->connection->query("SELECT $counter as num, $varcode as subgroup
                FROM $this->datatable
                WHERE $groupcode IS NOT NULL AND $filter AND $varcode IS NOT NULL
                GROUP BY $varcode");*/
        }
        else {
            $stmt = $this->query("SELECT $counter as num 
                FROM $this->datatable 
                WHERE $filter AND $var_code IS NOT NULL");
        }

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            $mainVar->addTotal($subgroup, $row['num']);
        }
    }

    /**
     * Get the number of students that selected an answer within the cutoff points.
     * @param CutoffVariable $variable
     * @param Variable|null $groupVar
     * @param string $filter
     * @param bool $addRollup Used in Highlights to add a rollup of the total number of students in the group.
     * @throws Exception
     */
    public function getCutoffPositives(CutoffVariable $variable, ?Variable $groupVar, string $filter, bool $addRollup = false): void
    {
        $cutoffQuery = "1";
        if($variable->low_cutoff != null) {
            $cutoffQuery .= " AND $variable->code >= $variable->low_cutoff";
        }
        if($variable->high_cutoff != null) {
            $cutoffQuery .= " AND $variable->code <= $variable->high_cutoff";
        }
        $withRollup = $addRollup ? 'WITH ROLLUP' : '';

        if($groupVar != null) {
            $stmt = $this->query("SELECT COALESCE(SUM(wgt),0) as num, $groupVar->code as subgroup
                FROM $this->datatable 
                WHERE $groupVar->code IS NOT NULL AND $cutoffQuery AND $filter
                GROUP BY $groupVar->code $withRollup");
        }
        else {
            $stmt = $this->query("SELECT COALESCE(SUM(wgt),0) as num
                FROM $this->datatable 
                WHERE $cutoffQuery AND $filter");
        }

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)) {
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            if($groupVar != null && $addRollup && $row['subgroup'] == null) //rollup total
                $subgroup = count($groupVar->labels);
            $variable->addCount($subgroup, $row['num']);
        }
    }

    /**
     * Get the total number of students, subject to the total cutoff.
     * @param CutoffVariable $variable
     * @param Variable|null $groupVar
     * @param string $filter
     * @param bool $addRollup Used in Highlights to add a rollup of the total number of students in the group.
     * @throws Exception
     */
    public function getCutoffTotal(CutoffVariable $variable, ?Variable $groupVar, string $filter, bool $addRollup = false): void
    {
        $cutoffQuery = "1";
        if($variable->total_cutoff != null) {
            $cutoffQuery .= " AND $variable->code >= $variable->total_cutoff";
        }
        $withRollup = $addRollup ? 'WITH ROLLUP' : '';

        if($groupVar != null) {
            $stmt = $this->query("SELECT COALESCE(SUM(wgt),0) as num, $groupVar->code as subgroup
                FROM $this->datatable 
                WHERE $variable->code IS NOT NULL AND $groupVar->code IS NOT NULL AND $cutoffQuery AND $filter
                GROUP BY $groupVar->code $withRollup");
        }
        else {
            $stmt = $this->query("SELECT COALESCE(SUM(wgt),0) as num
                FROM $this->datatable 
                WHERE $variable->code IS NOT NULL AND $cutoffQuery AND $filter");
        }

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)) {
            $subgroup = $groupVar == null ? 1 : $row['subgroup'];
            if($groupVar != null && $addRollup && $row['subgroup'] == null) //rollup total
                $subgroup = count($groupVar->labels);
            $variable->addTotal($subgroup, $row['num']);
        }
    }

    /**
     * Get the total number of students that did not answer one of the questions (null response).
     * @param MultiVariable $mainVar
     * @param MultiVariable|null $groupVar
     * @param string $filter
     * @return float
     * @throws Exception
     */
    public function getNoResponseCount(MultiVariable $mainVar, ?MultiVariable $groupVar, string $filter): float
    {
        //don't use weighting for demographic questions
        if($this->isUnweighted($mainVar->code))
            $counter = "COUNT(1)";
        else
            $counter = "COALESCE(SUM(wgt),0)";

        $var_code = $mainVar->code;

        if($groupVar != null)
        {
            $group_code = $groupVar->code;
            $stmt = $this->query("SELECT $counter as num FROM $this->datatable WHERE ($var_code IS NULL OR $group_code IS NULL) AND $filter");
        }
        else {
            $stmt = $this->query("SELECT $counter as num FROM $this->datatable WHERE ($var_code IS NULL) AND $filter");
        }

        return $stmt->fetch_row()[0];
    }

    /**
     * Create an SQL WHERE clause for the given filter.
     * @param int|null $grade
     * @param int|null $gender
     * @param int|null $race
     * @param int|null $sexual_orientation
     * @param int|null $pyramid
     * @param int|null $race_simplified
     * @param int|null $num_assets
     * @param int|null $transgender
     * @param int|null $disability
     * @return string
     */
    public function createFilterString(?int $grade, ?int $gender, ?int $race, ?int $sexual_orientation, ?int $pyramid = null, ?int $race_simplified = null,
                                       ?int $num_assets = null, ?int $transgender = null, ?int $disability = null): string
    {
        $filter = " 1 ";
        if ($grade != null)
            $filter .= " AND I2 = ".$this->connection->real_escape_string($grade);
        if ($gender != null) {
            if($this->year >= 2022)
                $filter .= " AND gender_nb = " . $this->connection->real_escape_string($gender);
            else
                $filter .= " AND I3 = " . $this->connection->real_escape_string($gender);
        }
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
        if ($transgender != null && $this->year >= 2021)
            $filter .= " AND I3A = ".$this->connection->real_escape_string($transgender);
        if ($disability !== null && $this->year >= 2023)
            $filter .= " AND disability_cat = ".$this->connection->real_escape_string($disability);
        return $filter;
    }

    /**
     * Run mysql query after escaping input
     * @param string $stmt
     * @param array|null $params
     * @return bool|mysqli_result
     * @throws Exception
     */
    private function query(string $stmt, array $params = null): mysqli_result|bool
    {
        $this->last_raw_statement = $stmt;
        $this->last_params = $params;
        $this->last_full_statement = 'Unassigned';

        if($params != null) {
            for($i=0; $i<count($params); $i++) {
                $val = $params[$i];
                if($val === null)
                    $val = 'NULL';
                if($val === true)
                    $val = 1;
                if($val === false)
                    $val = 0;
                $params[$i] = $this->connection->real_escape_string($val);
            }
            $this->last_params = $params;
            $positions = array();
            $lastPos = 0;

            while (($lastPos = strpos($stmt, '?', $lastPos))!== false) {
                $positions[] = $lastPos;
                $lastPos = $lastPos + 1;
            }
            if(count($positions) != count($params))
                throw new Exception("Unequal number of paramaters in Query: $stmt ||| ".count($positions)." expected, ".count($params)." received");

            //replace all ? marks starting from the end of the string
            for($i=count($positions)-1; $i>=0; $i--) {
                if($params[$i] === 'NULL')
                    $stmt = substr($stmt, 0, $positions[$i]) . 'NULL' . substr($stmt, $positions[$i] + 1);
                else
                    $stmt = substr($stmt, 0, $positions[$i]) ."'". $params[$i] ."'". substr($stmt, $positions[$i] + 1);
            }
        }

        $this->last_full_statement = $stmt;
        $result = $this->connection->query($stmt);
        $this->throwExceptionOnError();
        return $result;
    }

    /**
     * Fetch all rows from the query and map its values to the fields in the given class
     * @param mysqli_result $result
     * @param string $class
     * @return array
     */
    protected function fetchAllObjects(mysqli_result $result, string $class): array
    {
        $objs = [];
        $type_map = $this->getTypeMap($result);
        while($row = $result->fetch_object()) {
            $obj = new $class;
            foreach($row as $key => $value) {
                $obj->$key = $this->convertDataType($value, $type_map[$key]);
            }
            $objs[] = $obj;
        }
        $result->free_result();
        return $objs;
    }

    /**
     * Fetch one row from the query and map its values to the fields in the given class
     * @param mysqli_result $result
     * @param string $class
     * @return mixed Returns null if no rows in result set.
     */
    protected function fetchObject(mysqli_result $result, string $class): mixed
    {
        $type_map = $this->getTypeMap($result);
        if($row = $result->fetch_object()) {
            $obj = new $class;
            foreach($row as $key => $value) {
                $obj->$key = $this->convertDataType($value, $type_map[$key]);
            }
            $result->free_result();
            return $obj;
        }
        $result->free_result();
        return null;
    }

    /**
     * Get the types of the columns in the query
     * @param $result mysqli_result
     * @return array
     */
    protected function getTypeMap(mysqli_result $result): array
    {
        $map = [];
        $fields = $result->fetch_fields();
        foreach($fields as $field) {
            $map[$field->name] = $field->type;
        }
        return $map;
    }

    /**
     * Convert data returned by a MySQL query from a string to the type defined by the MySQL column
     * @param $val string|null
     * @param $type int
     * @return float|int|string|null
     */
    protected function convertDataType(?string $val, int $type): float|int|string|null
    {
        if($val == null)
            return null;
        if(in_array($type, [1,2,3,8,9,16])) //tinyint, smallint, int, bigint, mediumint
            return intval($val);
        if(in_array($type, [4,5,246])) //float, double, decimal
            return floatval($val);
        return $val;
    }

    /**
     * Utility function to throw an exception if an error occurs while running a mysql command.
     * @throws Exception
     */
    protected function throwExceptionOnError(): void
    {
        if (mysqli_error($this->connection)) {
            $msg = '<b>MySQL Error ' . mysqli_errno($this->connection) . ":</b> " . mysqli_error($this->connection) . '<br>';
            if(isset($this->last_full_statement)) {
                $msg .= '<b>Full statement:</b> ' . $this->last_full_statement . '<br>'
                    . '<b>Raw statement:</b> ' . $this->last_raw_statement . '<br>'
                    . '<b>Parameters:</b> [' . ($this->last_params==null ? '' : implode(', ', $this->last_params)) . ']';
            }
            throw new Exception($msg);
        }
    }
}