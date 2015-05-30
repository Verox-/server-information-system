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
    // New logwriter to... write logs...
    var $logger;

    // MySQL connetion resource
    var $con;

    // Last result.
    var $last_result;

    function __construct()
    {
        // Create the logwriter.
        $this->logger = new Logger("db.log");

        // We check which drivers are installed.
        if (function_exists('mysqli_connect')) {
            // Attempt the connection with mysqli driver. http://php.net/manual/en/mysqli.quickstart.connections.php
            $this->con = new mysqli($settings['db_host'], $settings['db_user'], $settings['db_pass'], $settings['db_database']);

            // Check if the connection succeded.
            if ($this->con->connect_errno) {
                // Oh no, we couldn't connect.
                $logger->Write("Failed to connect to MySQL: (" . $this->con->connect_errno . ") " . $this->con->connect_error);

                header("HTTP/1.0 500 Internal Server Error");
                echo "Failed to connect to MySQL: (" . $this->con->connect_errno . ") " . $this->con->connect_error;
                return;
            }

            // Connection success.
            return true;
        } else {
            $logger->Write("mysqli module has not been installed or is not active");

            header("HTTP/1.0 500 Internal Server Error");
            die("ERROR: mysqli module has not been installed or is not active");
        }
    }


    public function query($query)
    {
        // Execute the query, will return false if an error occurs.
        if (!$res = $this->con->query($query))
            $logger->Write("Failed to connect to MySQL: (" . $this->con->connect_errno . ") " . $this->con->connect_error);

        // Set the driver's internal result... thing.
        $this->last_result = $res;

        // No problem, return the result.
        return $this->result;
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


}

?>
