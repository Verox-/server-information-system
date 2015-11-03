<?php
$teststart = microtime(true);

// Define the master variable.
define('IN_SIM', true);

// Load the settings.
require_once __DIR__ . '/settings.php';

// Load the database driver.
require_once SIM_ROOT_DIR . 'modules/db.php';

// Init the db driver
$db = new DbDriver();

// Get all the missions. (Paginiation is for the... people who have time to implement it.)
$result = $db->query("SELECT * FROM aar_replays ORDER by id DESC");

if ($result->num_rows == 0)
{
	$missions = [0];
}

/* fetch object array */
while ($row = $result->fetch_assoc()) {
	$missions[] = $row;
}

/* free result set */
$result->close();

// Lazy settings
$settings = SIMRegistry::GetSettings();
?>
<html>
<head>
	<title><?=SIMRegistry::$settings['community_name']?> - Mission Replays</title>
	<link rel='stylesheet' type='text/css' href='./res/css/main.css'>
	<link rel='stylesheet' type='text/css' href='./res/css/missions.css'>
	<link rel="shortcut icon" href="<?=SIMRegistry::$settings['favicon']?>">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" async>
	<meta name="keywords" content="<?=SIMRegistry::$settings['community_name']?>, ARMA Replay">
    <meta charset="utf-8">
	<meta name="description" content="<?=SIMRegistry::$settings['community_name']?> Server Tracking System.">
	<meta name="author" content="Verox">
</head>
<body>
	<div id='uo_tools_navbar' class='navbar'>
	</div>
	<div id='page_content'>
		<div id='server_header'>
			<img src='./res/img/title_back.png'></img>
			<!---<span id='server_header_name'>## SERVER NAME ##</span> -->
		</div>
		<div id='error_panel'>
			<!-- <div class='errorBox'>This section is still under construction and may not be fully functional.</div> -->
			<?php if ($missions[0] == 0) { echo "<div class='warnBox'>The database is empty. If this is a new install this is expected, the database will populate when replays are recorded.</div>"; } ?>
		</div>
		<div class='navbox'>
		<a href='<?=SIMRegistry::$settings['community_url']?>'><h3 class='navbox_title'><?=SIMRegistry::$settings['community_name']?></h3></a><h3 class='navbox_content'> « Missions History</h3>
		</div>
		<div id='past_missions_list' class='outerBox'>
			<h3>Recent Mission History</h3>
			<div class='contentBox'>
				<table class='missionTable'>
					<thead>
						<tr><th>Mission Name</th><th style='text-align:center'>Replay</th><th style='text-align:center'>Start</th><th style='text-align:center'>End</th><tr>
					</thead>
					<?php
						if ($missions[0] == 0)
						{
							echo "<tr><td colspan='4' style='text-align: center;'><span style='color: red;'> </span></td></tr>";
						}
						else if (count($missions) == 0) {
							echo "<tr><td colspan='4' style='text-align: center;'><span style='color: red;'> ERROR! </span></td></tr>";
						} else {
							foreach ($missions as $mission) {
								// Temporary fix for map,
								if ($mission['island'] == "") { $mission['island'] = "<i>Unknown</i>"; }

								// Format date into a compressed format, use if more data needs to be displayed.
								// try {
								// 	$mstart = explode(" ", $mission['start']);
								// 	$mission['start'] = "<span style='font-size: 11px;'>" . $mstart[0] . "</span><br />" . $mstart[1] . "";
								// } catch (Exception $ex) { $msn_start_formatted = "ERROR"; }
								//
								// try {
								// 	$mend = explode(" ", $mission['end']);
								// 	$mission['end'] = "<span style='font-size: 11px;'>" . $mend[0] . "</span><br />" . $mend[1] . "Z"; //->format('Y/m/d') ->format('H:i')
								// } catch (Exception $ex) { $msn_start_formatted = "ERROR"; }

								echo "<tr><td>{$mission['filename']}<br /><span class='extra_missiontext'>Island: {$mission['island']}</span></td><td style='text-align:center'><a href=\"{$settings['base_url']}{$settings['replay_url']}{$mission['hash']}\"><span class=\"playBtn\"><i class=\"fa fa-play-circle\"></i> Play</span></a></td><td style='text-align:center'>{$mission['start']}Z</td><td style='text-align:center'>{$mission['end']}Z</td></tr>";
							}
						}
					?>
					<tfoot>
						<tr><td colspan='4' style="padding: 0px;">
						<div class='table_navbutton button left'>&laquo;&emsp;Prev Page </div>
						<div class='table_navdisplay'></div>
						<div class='table_navbutton button right'>Next Page&emsp;&raquo;</div></td></tr>
					</tfoot>
					<!--<tr><td>CO40 RodeoClowns v1.1.4</td><td>1 hour 24 minutes</td><td>24 players</td></tr>-->
				</table>
			</div>
		</div>
			<footer>
	- Server Information Manager - <br />
	Created by <a href='http://www.unitedoperations.net/' target='_blank'style='text-decoration: none; color: rgb(195, 121, 120);'>Verox@UO.net</a> for <?=SIMRegistry::$settings['community_name']?> - Visit us at <a href='<?=SIMRegistry::$settings['community_url']?>' target='_blank'style='text-decoration: none; color: rgb(195, 121, 120);'><?=SIMRegistry::$settings['community_url']?></a>!<br />
	This page was generated in <?php echo (microtime(true) - $teststart);?> seconds.
	</footer>
