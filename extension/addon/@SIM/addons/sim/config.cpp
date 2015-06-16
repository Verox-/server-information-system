////////////////////////////////////////////////////////////////////
// I HONESTLY DON'T KNOW WHAT ANY OF THIS DOES!
////////////////////////////////////////////////////////////////////

#define _ARMA_ // Uhm...


class CfgPatches
{
	class SIMExt
	{
		units[] = {};
		weapons[] = {};
		requiredVersion = 0.1;
		requiredAddons[] = {};
		author[] = {"Verox"};
		versionDesc = "Server Information Manager - AAR Module";
		versionAct = "";
		version = "1.0";
		versionStr = "1.0";
		versionAr[] = {1,0,0};
	};
};
class Extended_PostInit_EventHandlers
{
	class SIMExt
	{
		init = "call compile preProcessFileLineNumbers '\sim\init.sqf'";
	};
};