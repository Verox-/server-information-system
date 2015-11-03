// Get the download size and confirm the replay exists.
$.ajax({
    url: base_url + "/replays/" + replayIdentifierHash + ".replay",
    method: 'HEAD',
    async: false,
    success: function(data, status, request) {
        download_size = request.getResponseHeader('Content-Length');
        $("#mapContainer").append("<span class='consoleMessage'>Downloading and decompressing replay file (" + (download_size / 1000000).toFixed(2) + "MB)...</span>");
    },
    error:  function(request, status, error) {
        $("#mapContainer").append("<span class='consoleMessage'>Downloading and decompressing replay file...</span>");
        $("#mapContainer").append("<span class='consoleMessage'>failed.</span><br />");
        $("#mapContainer").append("<br /><span class='consoleErrorMessage'>An error occured attempting to retrieve replay metadata: " + request.status + " " + error + "</span><br />");
    }
});


var replay = "";

if (replay_base64 == "ERROR") {
    $("#mapContainer").append("<span class='consoleMessage'>failed.</span><br />");
    $("#mapContainer").append("<br /><span class='consoleErrorMessage'>No replay with that hash exists.</span><br />");
}
var replayFilePointer = 0;
var frames = [];
var parBuf = false;
var finalResult;
while (replayFilePointer != -1) {
    chunk = DownloadReplayChunk(replayFilePointer)
    console.log("chunkptr:" + chunk[0]);

    if (chunk[0] < 0)
    {
        finalResult = chunk;
        break;
    }

    frames = frames.concat(chunk[1].split("\n"));
    console.log("frames: " + frames.length);
    //replay += chunk[1];
    replayFilePointer = chunk[0];
}

if (finalResult[0] == -1)
{
    $("#mapContainer").append("<span class='consoleMessage'>done.</span><br />");
}
else if (finalResult[0] == -2)
{
    $("#mapContainer").append("<span class='consoleMessage'>failed.</span><br />");
    $("#mapContainer").append("<br /><span class='consoleErrorMessage'>Replay appears to be corrupted or in an invalid format: Server reported an unknown error.</span><br />");
    throw new Error("R014: FATAL ERROR IN REPLAY.");
}


$("#mapContainer").append("<span class='consoleMessage'>Parsing replay frames...</span>");
//var frames = replay.split("\n");

if (frames.length < 2) {
    $("#mapContainer").append("<span class='consoleMessage'>failed.</span><br />");
    $("#mapContainer").append("<br /><span class='consoleErrorMessage'>Replay appears to be corrupted or in an invalid format: Invalid number of frames (" + frames.length + ").</span><br />");
    $("#mapContainer").append("<br /><span class='consoleMessage'>If this is an older replay (<29/06/15) then it may be using the old format. Please ask your administrator to manually convert the replay.</span><br />");
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


if (!initResult) {
    console.log("ERROR: The map was unable to be initialized.");
    $("#mapContainer").append("<br /><span class='consoleErrorMessage'>The map was unable to be initialized.</span><br />");

    throw new Error("R013: MAP INIT FAILED.");
}

InitUIControl(frames.length);

function InitUIControl(frames) {
    $("#replaySeeker").attr("max", frames);
    window.alert("This tool is in Beta.\n\nPlease report any and all bugs, comments or suggestions on the bug tracker at \n  https://github.com/Verox-/aar \nor to Verox either in person or on the forum thread.\n\n");
    $(".controlsContainer").show(300);
}

function DownloadReplayChunk(seek)
{
    var chunk;
    var seeker;

    $.ajax({
        url: base_url + "/api/v1/DownloadReplay.php?id=" + replayIdentifierHash + "&seek=" + seek,
        method: 'GET',
        async: false,
    })
    .done(function( data ) {
        //$("#mapContainer").append("<span class='consoleMessage'>done.</span><br />");
        cdt = data.split(":", 2)
        seeker = cdt[0];
        chunk = JXG.decompress(cdt[1]);
    })
    .fail(function() {
        seeker = -2;
    });

    return [seeker, chunk];
}

function RunClock() {
    var buffer = new Array(); //tbi...
    var framePointer = initialFramePointer;
    var avgFrameDuration = (replayDuration / frames.length) * 100;
    var replayClock = null;
    var updateSeeker = true;

    $("#tTime").html(TimeStringify(frames.length * avgFrameDuration / 100, frames.length * avgFrameDuration / 100));
    $("#dTime").html(TimeStringify(framePointer * avgFrameDuration / 100, frames.length * avgFrameDuration / 100));
    //UpdateInterface();

    $("#staticLinkButton").click(function() {
        $("#staticLinkContainer").toggle(400);
        $("#staticLinkText").html("http://aar.unitedoperations.net/replay/" + replayIdentifierHash + "/frame/" + framePointer);
    });

    $("#staticLinkContainerClose").click(function() {
        $("#staticLinkContainer").hide(400);
    });


    function UpdateInterface() {
        if ($.isEmptyObject(frames[framePointer])) {
            return;
        }
        $("#dTime").html(TimeStringify(framePointer * avgFrameDuration / 100, frames.length * avgFrameDuration / 100)); //"T+" + Math.round(framePointer * avgFrameDuration/100) + "s"

        var frameJson = JSON.parse(frames[framePointer]);
        UpdateUnitMarkers(frameJson.units);

        if (frameJson.kills != undefined)
        {
            HandleKillEvents(frameJson.kills);
        }

    }

    $("#playPauseButton").click(function() {
        ToggleClock();
    });

    /**
     * Toggles the clock on or off.
     * Override === true turns the clock on, false off.
     */
    function ToggleClock(override) {
        if (replayClock != null || override === false) {
            clearInterval(replayClock);
            replayClock = null;
            $("#playPauseButton").html("<i class='fa fa-play'></i>");
            console.log("STOP THE CLOCK!");
        } else if (replayClock == null || override === true) {
            replayClock = setInterval(function() {
                //console.log(frames[framePointer]);
                if (updateSeeker) {
                    $("#replaySeeker").val(framePointer);
                }

                UpdateInterface();
                framePointer++;
                if (framePointer > frames.length) {
                    console.log("OMG STOP!");
                    $("#playPauseButton").html("<i class='fa fa-play'></i>");
                    clearInterval(replayClock);
                    replayClock = null;
                }
            }, avgFrameDuration);
            console.log("STAHT THE CLOCK!");
            $("#playPauseButton").html("<i class='fa fa-pause'></i>");
        }


    }

    // $( "#replaySeeker" ).change(function() {
    //
    // });
    var lastPlayState = false;
    $("#replaySeeker").mousedown(function() {
        $("#staticLinkContainer").hide();
        lastPlayState = (replayClock != null ? true : false);
        ToggleClock(false);
        console.log(lastPlayState);
        $("#replaySeeker").mousemove(function() {
            framePointer = Number($("#replaySeeker").val());
            UpdateInterface(); //console.log();
        });
        updateSeeker = false;
    });

    $("#replaySeeker").mouseup(function() {
        $("#replaySeeker").unbind('mousemove');
        framePointer = Number($("#replaySeeker").val());
        updateSeeker = true;
        ToggleClock(lastPlayState);
        UpdateInterface();
    });
}

function TimeStringify(sec, totalsec) {
    function pad(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }

    var hours = Math.floor(sec / 3600);
    sec = sec - (hours * 3600); //why didn't mod work...
    var minutes = Math.floor(sec / 60);
    var seconds = Math.floor(sec % 60);

    var result;
    var result = (totalsec >= 3600 ? pad(hours, 2) + "h " : "");
    result = result + (minutes != 0 ? pad(minutes, 2) + "m " : "00m ");
    result = result + (seconds != 0 ? pad(seconds, 2) + "s" : "00s");

    return result;
}

RunClock();
