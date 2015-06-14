<?php

// Script generation time
$teststart = microtime(true);

// Define the master variable.
define('IN_SIM', true);

// Load all the modules.
require_once './modules/all_modules.php';

// Create the database driver.
$db = new DbDriver();

// Get the server's basic info.
$basic_info = new ServerBasicInfo();
$serverinfo = $basic_info->GetServerInfo();
var_dump($serverinfo);


 ?>

 <html>
 <head>
     <title>SIM - Playthrough Information</title>
     <link rel='stylesheet' type='text/css' href='<?php echo SIM_DIR_CSS ?>main.css'>
     <link rel="shortcut icon" href="http://forums.unitedoperations.net/favicon.ico">
     <script src='<?php echo SIM_DIR_JS ?>countdown.min.js' type="text/javascript" async></script>
     <script src="<?php echo SIM_DIR_JS ?>jquery-2.1.1.js" type="text/javascript" defer></script>
     <meta name="keywords" content="United Operations, UOSTISS, UOSIM">
     <meta name="description" content="United Operations' Server Tracking System.">
     <meta name="author" content="Verox">
 </head>
     <body>
     <div id='uo_tools_navbar' class='navbar'>

     </div>
     <div id='page_content'>
         <div id='server_header'>
             <img src='<?php echo SIM_DIR_IMG ?>title_back.png' alt='United Operations Server Tracker'>
             <!---<span id='server_header_name'>## SERVER NAME ##</span> -->
         </div>

         <div id='error_panel'>
             <div class='errorBox'>OOPS! Extended functionality is broken for the time being.</div>
             <? echo $errors; ?>
         </div>
         <script>
         var t = "<? echo $serverinfo['last_updated']; ?>".split(/[- :]/);
         var e = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
         var d = new Date(e +" UTC");

         setInterval(function() {
                             $("#last_refreshed_timer").html( countdown( d ).toString() );
                         }, 1000);
         </script>
         <div style='display:table; '>
             <div id='server_information_panel'>
                 <div id='server_details'>
                     <div class='outerBox'>
                     <h3>Server Details<span id='last_refreshed' class='refresh_timer'>Last refreshed <span id='last_refreshed_timer'>00m 00s</span> ago.</span></h3>
                         <div class='contentBox'>
                             <div>
                                 <table style='table-layout: fixed; width: 100%;'>
                                     <tr><td class='sip_title'>Name:</td><td class='sip_value'><? echo $serverinfo['server_name']; ?></td></tr>
                                     <tr><td class='sip_title'>Players:</td><td class='sip_value'><? echo $serverinfo['players']; echo "/"; echo $serverinfo['max_players']; if ($serverinfo['players'] >= $serverinfo['max_players']) { echo " <span style='color: red;'>FULL!</span>"; } ?></td></tr>
                                     <tr><td class='sip_title'>Island:</td><td class='sip_value'><? echo $serverinfo['map']; ?></td></tr>
                                     <tr><td class='sip_title'>Mission:</td><td class='sip_value'><? echo $serverinfo['mission']; ?></td></tr>
                                     <tr><td class='sip_title'>Game Type:</td><td class='sip_value'><? echo $serverinfo['mission_information']['field_43']; ?></td></tr>
                                     <!--<tr><td class='sip_title'>Mission State:</td><td class='sip_value'><span style='font-style: italic;'>PROTOCOL_ERROR::NOT_IMPLEMENTED</span></td></tr>
                                     <tr><td class='sip_title'>Difficulty:</td><td class='sip_value'><span style='font-style: italic;'>PROTOCOL_ERROR::NOT_IMPLEMENTED</span></td></tr>-->
                                     <tr><td class='sip_title'>Mission Author:</td><td class='sip_value'><span><? echo $serverinfo['mission_information']['field_47']; ?></span></td></tr>
                                     <tr><td class='sip_title'>Mission Description:</td><td class='sip_value'><div style="overflow: overlay; height: 321px; word-wrap: break-word;"><? echo $serverinfo['mission_information']['field_49']; ?></div></td></tr>
                                 </table>
                             </div>
                         </div>
                     </div>
                 </div>
                 <div id='server_quickview' class='outerBox'>
                     <h3>Tools</h3>
                     <div class='contentBox'>
                         <img src='./resource/images/maps/<? (!$serverinfo['players'] ? $return = "empty" : $return = $serverinfo['map']); echo $return; ?>.png' style="margin: 10px;" width='250px'/><br />
                         <!-- <div style='word-wrap: break-word;'>100 people playing CO30_whateverthefuck_v2 on Altis.</div> -->
                         <div style='font-size: 11px; text-align: center;'>
                             <strong>Rate Mission</strong><br />
                             <img src='./resource/images/icons/star.png' alt='full_star'>
                             <img src='./resource/images/icons/star.png' alt='full_star'>
                             <img src='./resource/images/icons/star.png' alt='full_star'>
                             <img src='./resource/images/icons/star.png' alt='full_star'>
                             <img src='./resource/images/icons/star.png' alt='full_star'>
                         </div>
                         <div class='quickbuttons'>
                             <ul class='quickbuttons_list'>
                                 <li><a href='pws://six.unitedoperations.net/arma3/a3srv1.yml?action=update,join' >Join server with <img src='./resource/images/icons/six_logo.svg' width='20px' style='vertical-align: text-top;' alt='Play withSix'/></a></li><!--pws://six.armaseries.cz/a3/505.yml?action=update,join-->
                                 <li><a <? if ($serverinfo['players'] != 0) {echo "href='#' onClick=\"$('#report_form').submit();\"";} else {echo "class='disabled_button'";} ?> >Report mission as Broken</a></li>
                                 <li><a <? if ($serverinfo['players'] != 0) {echo "href='#'";} else {echo "class='disabled_button'";} ?> >Mission Statistics</a></li>
                                 <li><a href='http://forums.unitedoperations.net/index.php/page/ArmA3/missionlist/_/livemissions/<? echo $serverinfo['mission_information']['record_dynamic_furl'] . "-r" . $serverinfo['mission_information']['primary_id_field']; ?>' target='_blank'>View in mission database</a></li>
                             </ul>
                         </div>
                     </div>
                     <form id="report_form" action="http://forums.unitedoperations.net/index.php/page/MMO/ReportTool" method="POST" target="_blank">
                     <input type="hidden" name="mission" value="<? echo $serverinfo['mission_information']['field_38']; ?>">
                     <input type="hidden" name="link" value="<? echo "http://forums.unitedoperations.net/index.php/page/ArmA3/missionlist/_/livemissions/" . $serverinfo['mission_information']['record_dynamic_furl'] . "-r" . $serverinfo['mission_information']['primary_id_field']; ?>">
                     <input type="hidden" name="dbid" value="7">
                     <input type="hidden" name="recordID" value="<? echo $serverinfo['mission_information']['primary_id_field']; ?>">
                     </form>
                     <!--- 100 people playing CO30_whateverthefuck_v2 on Altis. -->
                 </div>
             </div>
         </div>
     </body>
 </html>
