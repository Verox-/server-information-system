_uid = _this;

_clientId = -1;
_cycles = 0;

while{_clientId == -1}do
{
  {
     if (getPlayerUID _x == _uid) exitWith
     {
        _clientId = owner _x;
		_x addMPEventHandler ["MPKilled",{diag_log "SIM_AAR: Fired MPEventHandler outside isDed loop."; if (isDedicated) then {  diag_log "SIM_AAR: Fired MPEventHandler in isDed loop."; _killJson = format["{""time"": ""%1"",""killer"": {""nid"": ""%2"", ""uid"": ""%3"",""pos"": ""%4"", ""fac"": ""%5"", ""name"": ""%6""},""victim"": {""nid"": ""%7"", ""uid"": ""%8"",""pos"": ""%9"", ""fac"": ""%10"", ""name"": ""%11""}}", time, netId (_this select 1), getPlayerUID (_this select 1), getPosASL (_this select 1), side (_this select 1), name (_this select 1), netId (_this select 0), getPlayerUID (_this select 0), getPosASL (_this select 0), side (_this select 0), name (_this select 0)];"SIMExt" callExtension format["K%1", _killJson];};}];
		diag_log "SIM_AAR: Added MPEventHandler";
     };
  } forEach playableUnits;
  _cycles = _cycles + 1;
  sleep 1;
};

diag_log format["JIP: _cycles =  %1",_cycles];
