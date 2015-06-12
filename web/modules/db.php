<?php // Database wrapper for MySQL.

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
        if ($self != null) {
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
