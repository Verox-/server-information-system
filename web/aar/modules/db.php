<?php

/* LICENCE
// Drop-in database driver for SIM.
// Copyright (C) 2015 - Jerrad 'Verox' Murphy
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>. */

if (!defined("IN_SIM")) {
	die("SIM did not fully initialise. Did you directly access this file?");
}

// Make sure the settings file is loaded.
require_once SIM_ROOT_DIR . 'settings.php';

// Load the logwriter.
require_once SIM_ROOT_DIR . 'modules/logger.php';

class DbDriver
{
    // Static instance of myself.
    static $self;

    // New logwriter to... write logs...
    var $logger;

    // MySQL connetion resource
    var $con;

    // Last result.
    var $last_result;

    function __construct()
    {
        // Check if we already exist.
        if (self::$self != null) {
            return $this->self;
        }

        // Create the logwriter.
        $this->logger = new Logger("db.log");

        // We check which drivers are installed.
        if (function_exists('mysqli_connect')) {
            // Attempt the connection with mysqli driver. http://php.net/manual/en/mysqli.quickstart.connections.php
            $this->con = new mysqli(SIMRegistry::$settings['db_host'], SIMRegistry::$settings['db_user'], SIMRegistry::$settings['db_pass'], SIMRegistry::$settings['db_database']);

            // Check if the connection succeded.
            if ($this->con->connect_errno) {
                // Oh no, we couldn't connect.
                $this->logger->Write("Failed to connect to MySQL: (" . $this->con->connect_errno . ") " . $this->con->connect_error);

                header("HTTP/1.0 500 Internal Server Error");
                echo "Failed to connect to MySQL: (" . $this->con->connect_errno . ") " . $this->con->connect_error;
                return;
            }

            // Connection success.
            return true;
        } else {
			$this->logger->Write("mysqli module has not been installed or is not active");

            header("HTTP/1.0 500 Internal Server Error");
            die("ERROR: mysqli module has not been installed or is not active");
        }
    }


    public function query($query)
    {
        // Execute the query, will return false if an error occurs.
        if (!$this->last_result = $this->con->query($query))
			echo "Failed to query MySQL: (" . $this->con->error . ")"; //$this->logger->Write("Failed to connect to MySQL: (" . $this->con->connect_errno . ") " . $this->con->connect_error);

        // No problem, return the result.
        return $this->last_result;
    }

	/**
	 * Insert a series of key-value pairs into the database.
	 * @param  string $table Table name to insert into.
	 * @param  array  $a     Key-Value pairs (corresponding to collumn => value) to insert into the database.
	 * @return bool          True if insert success, false otherwise.
	 */
	public function insert($table, $vals)
	{
		$columns= "";
		$values= "";

		$table = $this->con->real_escape_string($table);

		foreach ($vals as $col => $val)
		{
			$columns .= "`" . $this->con->real_escape_string($col) . "`,";
			$values .=  "'" . $this->con->real_escape_string($val) . "',";
		}

		$columns = trim($columns, ',');
		$values = trim($values, ',');
		$query = "INSERT INTO `{$table}` ({$columns}) VALUES ({$values})";

		return ($this->query($query) ? true : false);
	}

	public function update($table, $vals, $condition)
	{
		// This function REQUIRES a condition.
		if ($condition == null || empty($condition))
		{
			return false;
		}

		$updates= "";

		$table = $this->con->real_escape_string($table);

		foreach ($vals as $col => $val)
		{
			$updates .= "`" . $this->con->real_escape_string($col) . "`='" . $this->con->real_escape_string($val) . "',";
		}

		$updates = trim($updates, ',');

		$query = "UPDATE `{$table}` SET {$updates} WHERE {$condition}";

		return ($this->query($query) ? true : false);
	}

    public function prepared_query()
    {
        return false;
    }

    public function fetch()
    {
        return $this->last_result->fetch_assoc();
    }

    public function fetch_resource(&$resource)
    {
        return $resource->fetch_assoc();
    }

    function num_rows() {
        if ($this->last_result)
            return $this->last_result->num_rows;

        return false;
    }

    function getError() {
        return $this->con->error;
    }

    public static function getDriver()
    {
        // Check if we already exist, if we don't make me.
        if (self::$self == null) {
            self::$self = new DbDriver();
        }

        // Return a reference to this object.
        return self::$self;
    }

    public function queryIPB($query)
    {
        // Check if we're actually using the IPBoard database or not.
        if (!SIMRegistry::$settings['using_ips'])
        {
            return false;
        }

        // Switch to the IPBoard database, if it fails return false.
        if (!$this->con->select_db(SIMRegistry::$settings['ips']['db']))
        {
            return false;
        }

        // Perform the query.
        $result = $this->query($query);

        // Switch back to the SIM database.
        $this->con->select_db(SIMRegistry::$settings['db_database']);

        // Pass the result back.
        return $result;
    }


}

?>
