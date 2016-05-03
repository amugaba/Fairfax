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
        $stmt = $this->connection->prepare("SELECT autoid, code, question FROM variables");
        $this->throwExceptionOnError();

        $stmt->execute();
        $this->throwExceptionOnError();

        $vars = [];
        $var = new VariableVO();
        $stmt->bind_result($var->autoid, $var->code, $var->question);

        while ($stmt->fetch())
        {
            $vars[] = $var;
            $var = new VariableVO();
            $stmt->bind_result($var->autoid, $var->code, $var->question);
        }

        return $vars;
    }

    /**
     * Get answer labels of one questions
     * @param int $varid
     * @return string[]
     */
    public function getLabels($varid)
    {
        $varid = $this->connection->escape_string($varid);

        $stmt = $this->connection->query("SELECT answer1,answer2,answer3,answer4,answer5,answer6,answer7,answer8,answer9,
            answer10,answer11,answer12,answer13,answer14,answer15,answer16,answer17,answer18,answer19,answer20,answer21,
            answer22,answer23,answer24,answer25,answer26 FROM variables WHERE autoid=$varid");
        $this->throwExceptionOnError();

        return $stmt->fetch_row();
    }

    public function getData($varcode)
    {
        $varcode = $this->connection->escape_string($varcode);

        $stmt = $this->connection->query("SELECT $varcode as answer, COUNT(1) as num FROM data GROUP BY $varcode");
        $this->throwExceptionOnError();

        $result = [];

        while($row = $stmt->fetch_assoc())
            $result[] = $row;
        return $result;
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