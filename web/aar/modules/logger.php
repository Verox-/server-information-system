<?php

// Provides an easy-to-use interface to log information.

if (!defined("IN_SIM")) {
	die("SIM did not fully initialise. Did you directly access this file?");
}

class Logger
{
    var $logpath;

    function __construct($log_name) {
        if (!is_writable(SIM_ROOT_DIR . SIMRegistry::$settings['log_dir'] . $log_name)) {
            $this->logpath = SIM_ROOT_DIR . SIMRegistry::$settings['log_dir'] . $log_name;
            return false;
        }
        
        $this->logpath = SIM_ROOT_DIR . SIMRegistry::$settings['log_dir'] . $log_name;
    }

    public function Write($line)
    {
        if (empty($this->logpath))
            echo "Log failure."; return;

        $line = gmdate("Y-m-d H:i:s") . " - " . $line . PHP_EOL;

        file_put_contents($this->logpath, $line, FILE_APPEND);
    }
}

 ?>
