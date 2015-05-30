<?php // Database wrapper for MySQL.

if (!IN_SIM) {
	die("SIM did not fully initialise. Did you directly access this file?");
}

// Make sure the settings file is loaded.
require_once '../settings.php';

// Load the logwriter.
require_once './logger.php';

class DbDriver
{
    // New logwriter to... write logs...
    var $logger;

    // MySQL connetion resource
    var $con;

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
                die("Failed to connect to MySQL: (" . $this->con->connect_errno . ") " . $this->con->connect_error);
            }

            // Connection success.
            return true;
        } else {
            header("HTTP/1.0 500 Internal Server Error");
            die("ERROR: mysqli module has not been installed or is not active");
        }
    }

    public function Query($query)
    {
        // Raw query here.
    }

    public function PreparedQuery()
    {
        # code...
    }

}

?>
