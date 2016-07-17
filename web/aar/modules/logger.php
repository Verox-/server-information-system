<?php
// /* LICENCE
// Provides an easy-to-use interface to log information.
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
