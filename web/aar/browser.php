<?php
// Define the master variable.
define('IN_SIM', true);

// Load the settings.
require_once __DIR__ . 'settings.php';

// Load the database driver.
require_once SIM_ROOT_DIR . 'modules/db.php';

// Init the db driver
$db = new DbDriver();

// Get all the missions. (Paginiation is for the... people who have time to implement it.)
$result = $db->query("SELECT * FROM aar_replays ORDER by id DESC");

/* fetch object array */
while ($row = $result->fetch_assoc()) {
	$missions[] = $row;
}

/* free result set */
$result->close();

/* close connection */
$mysqli->close();
?>

<?php
	// Script generation time
	$teststart = microtime(true);

	$generationtime = microtime(true) - $teststart;
?>
<html>
<head>
	<title>Rifling Matters - Mission Replays</title>
	<link rel='stylesheet' type='text/css' href='./res/css/main.css'>
	<link rel='stylesheet' type='text/css' href='./res/css/missions.css'>
	<!-- <link rel="shortcut icon" href="http://forums.unitedoperations.net/favicon.ico">  CHANGE ME! -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" async>
	<meta name="keywords" content="Rifling Matters, RFM">
    <meta charset="utf-8">
	<meta name="description" content="Rifling Matters' Server Tracking System.">
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
			<? //echo <div class='errorBox'>This section is still under construction and may not be fully functional.</div> ?>
		</div>
		<div class='navbox'>
		<a href='http://riflingmatters.com/'><h3 class='navbox_title'>Rifling Matters</h3></a><h3 class='navbox_content'> « Missions History</h3>
		</div>
		<div id='past_missions_list' class='outerBox'>
			<h3>Recent Mission History</h3>
			<div class='contentBox'>
				<table class='missionTable'>
					<tr style='background: rgb(79, 77, 72);'><th>Mission Name</th><th style='text-align:center'>Replay</th><th style='text-align:center'>Start</th><th style='text-align:center'>End</th><tr>
					<?
						if (count($missions) == 0) {
							echo "<tr><td colspan='4' style='text-align: center;'><span style='color: red;'> ERROR GETTING RECENT MISSIONS! </span></td></tr>";
						} else {
							foreach ($missions as $mission) {
								// Temporary fix for map,
								if ($mission['map'] == "") { $mission['map'] = "<i>NULL</i>"; }
								try {
									$msn_start_formatted = "<span style='font-size: 11px;'>" . $mission['msn_start'] . "</span><br />" . $mission['msn_start'] . "";
								} catch (Exception $ex) { $msn_start_formatted = "ERROR"; }

								try {
									$msn_end_formatted = "<span style='font-size: 11px;'>" . $mission['msn_end'] . "</span><br />" . $mission['msn_end'] . "Z"; //->format('Y/m/d') ->format('H:i')
								} catch (Exception $ex) { $msn_start_formatted = "ERROR"; }

								echo "<tr><td>{$mission['filename']}<br /><span class='extra_missiontext'>Island: {$mission['island']}</span></td><td style='text-align:center'><a href=\"http://aar.unitedoperations.net/replay/{$mission['hash']}\"><span class=\"playBtn\"><i class=\"fa fa-play-circle\"></i> Play</span></a></td><td style='text-align:center'>{$mission['start']}Z</td><td style='text-align:center'>{$mission['end']}Z</td></tr>";
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
	- Server Information Manager for Rifling Matters - <br />
	Created by <a href='http://www.unitedoperations.net/' target='_blank'style='text-decoration: none; color: rgb(195, 121, 120);'>Verox@UO.net</a> for Rifling Matters - Visit us at <a href='http://riflingmatters.com/' target='_blank'style='text-decoration: none; color: rgb(195, 121, 120);'>riflingmatters.com</a>!<br />
	This page was generated in <? echo $generationtime; ?> seconds.
	</footer>
