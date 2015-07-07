$("#mapContainer").append("<span class='consoleMessage'>Decompressing replay file...</span>");
if (replay_base64 == "ERROR") {
    $("#mapContainer").append("<span class='consoleMessage'>failed.</span><br />");
    $("#mapContainer").append("<br /><span class='consoleErrorMessage'>No replay with that hash exists.</span><br />");
}
var replay = JXG.decompress(replay_base64);
$("#mapContainer").append("<span class='consoleMessage'>done.</span><br />");


$("#mapContainer").append("<span class='consoleMessage'>Parsing replay frames...</span>");
var frames = replay.split("\n");

if (frames.length < 2)
{
    $("#mapContainer").append("<span class='consoleMessage'>failed.</span><br />");
    $("#mapContainer").append("<br /><span class='consoleErrorMessage'>Replay appears to be corrupted or in an invalid format: Invalid number of frames ("+frames.length+").</span><br />");
    $("#mapContainer").append("<br /><span class='consoleMessage'>If this is an older replay (<29/06/15) then it may be using the old format. You can try to fix it by clicking this link(soon), waiting a few seconds then retrying the aar.</span><br />");
    throw new Error("R012: INVALID FRAMES.");
}
$("#mapContainer").append("<span class='consoleMessage'>done.</span><br />");
replay = undefined; // Garbage collection.

// document.write("Number of parsed frames: " + frames.length + "<br />");
// document.write("Mission: " + msnInfo.mission + "<br />");
// document.write("Duration (s): " + msnLastFrame.time);

// Vars to be set.
var replayDuration;
var initResult = InitMapFromReplay(frames[0], frames[frames.length - 2], frames.length);

if (!initResult)
{
    console.log("ERROR: The map was unable to be initialized.");
    $("#mapContainer").append("<br /><span class='consoleErrorMessage'>The map was unable to be initialized.</span><br />");

    throw new Error("R013: MAP INIT FAILED.");
}

InitUIControl(frames.length);

function InitUIControl(frames)
{
    $("#replaySeeker").attr("max", frames);
    window.alert("This tool is not yet ready for public use, it's buggy, unfinished and liable to break at any second as I make changes.\nIf you have been given access to this I would appreciate it if you did not freely share links to this tool with the general population until it has been completed.\n Thanks, \n -Verox");
    $(".seekerContainer").show(300);
}
var buffer = new Array(); //tbi...
var framePointer = 1;
var avgFrameDuration = (replayDuration / frames.length) * 100;

var replayclock = setInterval(function() {
    console.log(frames[framePointer]);
    $("#replaySeeker").val(framePointer);
    $("#dTime").html("T+" + Math.round(framePointer * avgFrameDuration/100) + "s");
    UpdateMapMarkers(JSON.parse(frames[framePointer]).units);
    framePointer++;
    if (framePointer > frames.length)
    {
        $( "#stopButton" ).remove();
        clearInterval(replayclock);
    }
}, avgFrameDuration);

$( "#stopButton" ).click(function() {
  clearInterval(replayclock);
  $( "#stopButton" ).remove();
  window.alert("Ok, but I can't... staht(?) again, yet.");
});

$( "#replaySeeker" ).change(function() {
    framePointer = Number($( "#replaySeeker" ).val());
});
