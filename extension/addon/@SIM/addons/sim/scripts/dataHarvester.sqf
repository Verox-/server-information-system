// SIM_AAR DATA HARVESTER
// AUTHOR: VEROX
// NOTES:
//			Can we loop every unit THEN call the extension and pass the data?
//			Would this be faster?
//			Is there a limit to the string size we can pass?
// 			Can we loop a few times THEN send to the extension to get the best of both worlds?
//
//	TODO:	The SLEEP command needs to be variable, to take into account LOAD and PIPE messages passed back from the extension.

// We sleep to wait until mission start. No need to know movements at briefing.
sleep 1;

// Output to the RPT that we're starting.
diag_log "SIM_AAR: Starting data harvester.";

// Let the extension know we're starting a new mission.
"SIMExt" callExtension format["B%1.%2", missionName, worldName];

// Sleep for a few seconds to prevent race.
uiSleep 3;

// Clientstate on the server will be "BRIEFING READ" as soon as the mission is loaded all the way to debreifing.
while {getClientState == "BRIEFING READ"} do {
		
	// Let the extension know we're starting a new loop.
	"SIMExt" callExtension format["S%1", time];
	
	// -- Start the unit loop --
	{
		// Get the data for current unit and format it in spec. format.
		_data = format["U""nid"": ""%1"",""uid"": ""%2"",""pos"": ""%3"", ""fac"": ""%4"", ""dir"": ""%5"", ""name"": ""%6"", ""group"": ""%7""", netId _x, getPlayerUID _x, getPosASL _x, side _x, getDir _x, name _x, group _x];
		
		// Send vehicle data if needed.
		if (vehicle _x != _x) then 
		{
			_thisVehicleDisplayName = getText (configFile >> "cfgVehicles" >> typeOf vehicle _x >> "displayName");
			// _fixedAssignedVehRole = [str assignedVehicleRole player, """", "'"] call CBA_fnc_replace; // HOW DO ESCAPE STTRRIINGS IN ARMA!?!?!
			_data = format["%1, ""vehicle"": {""nid"": ""%2"", ""vehicle_role"": ""%3"", ""dir"":""%4"",""displayName"": ""%5""}", _data, netId vehicle _x, (assignedVehicleRole _x) select 0, getDir vehicle _x, _thisVehicleDisplayName];
		};
		
		// Send this unit's data to the extension.
		"SIMExt" callExtension _data;
	} forEach allUnits;
	
	{
		// Get the data for current unit and format it in spec. format.
		_groupstr = format["G""nid"":""%1"",""leader"":""%2"",""groupid"":""%3""", netId _x, netId leader _x, groupId _x];
		
		// Send this unit's data to the extension.
		"SIMExt" callExtension _groupstr;
	} forEach allGroups;
	
	// Log the FPS of the server.
	"SIMExt" callExtension format["L%1", diag_fps];
	
	// Let the extension know that's everything.
	"SIMExt" callExtension "E";

	// Sleep for some seconds.
	uiSleep 3;
};

// We're exiting the mission.
"SIMExt" callExtension format["F%1", time];

diag_log "SIM_AAR: Ending data harvester.";