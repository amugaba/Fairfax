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
    public string $port;
    public string $username;
    public string $password;
    public string $server;
    public string $databasename;

    public function __construct ()
    {
        $this->username = $_ENV['MYSQL_USERNAME'];
        $this->password = $_ENV['MYSQL_PASSWORD'];
        $this->server = $_ENV['MYSQL_HOST'];
        $this->databasename = $_ENV['MYSQL_DATABASE'];
        $this->port = $_ENV['MYSQL_PORT'];
    }
}