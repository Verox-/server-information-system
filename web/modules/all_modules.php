<?php

if (!defined("IN_SIM")) {
	die("SIM did not fully initialise. Did you directly access this file?");
}

// Load the settings, This must be done first.
require_once 'settings.php';

// Load the logwriter. Optional, classes that need it should include it themselves.
require_once SIM_ROOT_DIR . 'modules/logger.php';

// Require the database driver.
require_once SIM_ROOT_DIR . 'modules/db.php';

// Require the server information module.
require_once SIM_ROOT_DIR . 'modules/server_info.php';

?>
