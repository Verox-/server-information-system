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
	"SIMExt" callExtension "B";

while {true} do {

	// Let the extension know we're starting a new loop.
	"SIMExt" callExtension format["S%1", time];
	
	// -- Start the unit loop --
	{
		// Get the data for current unit and format it in spec. format.
		_data = format["""nid"": ""%1"",""uid"": ""%2"",""pos"": ""%3"", ""fac"": ""%4"", ""dir"": ""%5""", netId _x, getPlayerUID _x, getPosASL _x, side _x, getDir _x];
		
		// Send this unit's data to the extension.
		"SIMExt" callExtension _data;
	} forEach allUnits;
	
	// Let the extension know that's all the units.
	"SIMExt" callExtension "E";
	
	// Sleep for some seconds.
	uiSleep 3;
}

// We're exiting the mission.
	"SIMExt" callExtension format["F%1", time];

diag_log "SIM_AAR: Ending data harvester.";