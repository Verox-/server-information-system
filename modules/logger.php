<?php

// Provides an easy-to-use interface to log information.

class Logger
{
    var $logpath;

    function __construct($log_name) {
        if (!is_writable(SIM_ROOT_DIR . "/logs/" . $log_name)) {
            return false;
        }

        $this->logpath = SIM_ROOT_DIR . "/logs/" . $log_name;
    }

    public function Write($line)
    {
        $line = gmdate("Y-m-d H:i:s") . " - " . $line . PHP_EOL;

        file_put_contents($this->logpath, $line, FILE_APPEND);
    }
}

 ?>
