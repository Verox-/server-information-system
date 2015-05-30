<?php // Database wrapper for MySQL.

if (!IN_SIM) {
	die("SIM did not fully initialise. Did you directly access this file?");
}

class DbDriver
{
    function __construct()
    {
        // Connect to the database here.
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
