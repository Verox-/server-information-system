diag_log "SIM_AAR: Init";
["simsEHonConnect", "onPlayerConnected", {_uid execVM "\sim\scripts\getPlayerObjectOnConnect.sqf"}] call BIS_fnc_addStackedEventHandler;
[] execVM "\sim\scripts\dataHarvester.sqf";