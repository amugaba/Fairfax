<?php

/**
 * 
 * Enter your MySQL database connection info below.
 * Username and password are for the user account your created with privileges
 * to edit the database
 * Server is the MySQL server location.
 * Databasename is the name of the schema you created.
 * Port is the port used to connect to the MySQL database.
 * @author tiddd
 *
 */

class ConnectionManager
{
    private $production = false;

    public $username = "root";
    public $password = "asdf2423";
    public $server = "127.0.0.1";
    public $databasename = "fairfax";
    public $port = "3306";

    public function __construct ()
    {
        if($this->production) {
            $this->username = "amugaba";
            $this->password = "overalls are in style";
            $this->server = "mysql.angstrom-software.com";
            $this->databasename = "fairfaxdb";
        }
        else {
            $this->username = "root";
            $this->password = "asdf2423";
            $this->server = "127.0.0.1";
            $this->databasename = "fairfax";
        }
    }
}