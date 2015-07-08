$("#mapContainer").append("<span class='consoleMessage'>Decompressing replay file...</span>");
if (replay_base64 == "ERROR") {
    $("#mapContainer").append("<span class='consoleMessage'>failed.</span><br />");
    $("#mapContainer").append("<br /><span class='consoleErrorMessage'>No replay with that hash exists.</span><br />");
}
var replay = JXG.decompress(replay_base64);
$("#mapContainer").append("<span class='consoleMessage'>done.</span><br />");


$("#mapContainer").append("<span class='consoleMessage'>Parsing replay frames...</span>");
var frames = replay.split("\n");

if (frames.length < 2) {
    $("#mapContainer").append("<span class='consoleMessage'>failed.</span><br />");
    $("#mapContainer").append("<br /><span class='consoleErrorMessage'>Replay appears to be corrupted or in an invalid format: Invalid number of frames (" + frames.length + ").</span><br />");
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


if (!initResult) {
    console.log("ERROR: The map was unable to be initialized.");
    $("#mapContainer").append("<br /><span class='consoleErrorMessage'>The map was unable to be initialized.</span><br />");

    throw new Error("R013: MAP INIT FAILED.");
}

InitUIControl(frames.length);

function InitUIControl(frames) {
    $("#replaySeeker").attr("max", frames);
    window.alert("This tool is in closed alpha; it may work, not work correctly, or not work at all.\n\nPlease report any and all bugs, comments or suggestions on the bug tracker at \n   https://github.com/Verox-/server-information-system\nor, if you do not have access, to Verox either in person or on the forum thread.\n\n");
    $(".controlsContainer").show(300);
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

    function UpdateInterface() {
        if ($.isEmptyObject(frames[framePointer])) {
            return;
        }
        $("#dTime").html(TimeStringify(framePointer * avgFrameDuration / 100, frames.length * avgFrameDuration / 100)); //"T+" + Math.round(framePointer * avgFrameDuration/100) + "s"
        UpdateMapMarkers(JSON.parse(frames[framePointer]).units);
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
}

RunClock();
