/* LICENCE
// Extension to plug into arma.
// Copyright (C) 2015 - Jerrad 'Verox' Murphy
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>. */

// http://maca134.co.uk/tutorial/write-an-arma-extension-in-c-sharp-dot-net/ - Thanks!
using RGiesecke.DllExport; //https://sites.google.com/site/robertgiesecke/Home/uploads/unmanagedexports

using System;
using System.IO.Pipes;
using System.IO;
//using System.Linq;
using System.Runtime.InteropServices;
using System.Text;
//using System.Threading.Tasks;

namespace SIMExt
{
    public class DllEntry
    {
        const string unique_server_id = "SRV1";

        // Constant JSON structure.


        const string json_data_footer = "]";

        static bool ready = true;
        static string mission_playthrough_hash;
        static string last_fps = "50";

        // Strinbuilder for fun.
        static StringBuilder datastring = new StringBuilder();
        static StringBuilder groups = new StringBuilder();
        static StringBuilder units = new StringBuilder();
        static StringBuilder kills = new StringBuilder();

        static NamedPipeClientStream pipe = new NamedPipeClientStream(".", "SIMSRV1", PipeDirection.Out, PipeOptions.WriteThrough);
        static StreamWriter ss = new StreamWriter(pipe);

        [DllExport("_RVExtension@12", CallingConvention = System.Runtime.InteropServices.CallingConvention.Winapi)]
        public static void RVExtension(StringBuilder output, int outputSize, [MarshalAs(UnmanagedType.LPStr)] string function)
        {
#if DEBUG
            DebugToFile(function);
#endif
            if (function[0] == 'U')
            {
                units.Append('{');
                units.Append(function.Substring(1));
                units.Append("},");
            }
            else if (function[0] == 'G')
            {
                groups.Append('{');
                groups.Append(function.Substring(1));
                groups.Append("},");
            }
            else if (function[0] == 'S') // Started a unit report.
            {
                const string json_data_header = "\"time\":";

                if (!pipe.IsConnected) { HandleBrokenConnection(); } // Check the pipe is still connected, if it isn't try to reconnect.

                if (!ready)
                {
                    datastring.Clear();
                }

                // Append the time field.
                datastring.Append(json_data_header + function.Substring(1));

                ready = false;
            }
            else if (function[0] == 'E') // Ended a unit report.
            {
                // If the mission start isn't set then this loop is broken. Exit immediatley.
                if (mission_playthrough_hash == null)
                    return;

                // Get rid of the last comma to make it valid json.
                if (datastring[datastring.Length - 1] != ',')
                {
                    datastring.Append("," + FinalizeJSONArrayOutput(ref units, "units"));
                }

#if DEBUG
                DebugToFile("OE: " + datastring.ToString() + "\n");
#endif
                // Append the groups json, if it exists.
                if (groups.Length > 1)
                {
                    datastring.Append("," + FinalizeJSONArrayOutput(ref groups, "groups"));
                }

                // Append the kills json, if it exists.
                if (kills.Length > 1)
                {
                    datastring.Append("," + FinalizeJSONArrayOutput(ref kills, "kills"));
                }

#if DEBUG
                DebugToFile("OP: " + datastring.ToString() + "\n");
#endif

                // Send the info to the daemon.
                if (!SendToDaemon("update", datastring.ToString()))
                {
                    // Something went wrong.
                    output.Append("PIPE");
                }

                // All done, wipe it away.
                datastring.Clear();

                ready = true;
            }
            else if (function[0] == 'K') // Started a new mission.
            {
                if (kills.Length > 1)
                {
                    kills.Append(',');
                }

                kills.Append(function.Substring(1));
            }
            else if (function[0] == 'B') // Started a new mission.
            {
                var mission = function.Substring(1);

                // Set the mission start timestamp.
                mission_playthrough_hash = GenerateMD5UniqueID(mission);

                SendToDaemon("start_mission", "\"mission\": \"" + mission + "\"");
            }
            else if (function[0] == 'F') // Finished the current mission.
            {
                if (!SendToDaemon("end_mission", "\"time\":" + function.Substring(1)))
                {
                    // Something went wrong.
                    output.Append("PIPE");
                }

                // Reset the mission start timestamp.
                mission_playthrough_hash = null;
            }
            else if (function[0] == 'L') // Load indicator (fps).
            {
                last_fps = function.Substring(1);
            }
            else
            {

            }

            outputSize = output.Length;
        }

        /// <summary>
        ///
        /// </summary>
        /// <param name="data">StringBuilder containing {object},{object},{object},</param>
        /// <param name="key">Key for the json array.</param>
        /// <returns>String of formatted JSON data. "key": [{object},{object},{object}]</returns>
        private static string FinalizeJSONArrayOutput(ref StringBuilder data, string key)
        {

            const string jsonArrayFormat = "\"{0}\": [{1}]";

            // Stringify the stringbuilder.
            string processedData = data.ToString();

            // Trim any commas to make it valid json.
            processedData = processedData.Trim(',');

            // Clear out the stringbuilder, it's data has been processed.
            data.Clear();

#if DEBUG
            DebugToFile("DS:" + processedData + "\n");
#endif

            // Return the json array.
            return string.Format(jsonArrayFormat, key, processedData);
        }

        // Put this in it's own thread. Should improve speedyness. Or use FlushAsync.
        private static bool SendToDaemon(string event_type, string data, bool retry = false)
        {
            // Wrap the data.
            string data_to_send = "{\"server_id\": \"" + unique_server_id + "\",\"event\":\"" + event_type + "\",\"hash\": \"" + mission_playthrough_hash + "\",\"fps\": " + last_fps + ",\"data\":{" + data + "}}\n";

            // Write and flush to the pipe. This needs to be async.
            try
            {
                ss.Write(data_to_send);
                ss.Flush(); // Flush the streamwriter's buffer to the pipe...
                pipe.Flush(); // ... and flush the pipe's buffer to send it.
            }
            catch
            {
                // Something went wrong, most likely the pipe closed (we need a specific catch for that)

                // If this is the second attempt then give up and return false.
                if (retry)
                    return false;

                // Try to reconnect the pipe.
                HandleBrokenConnection();

                // then try again...
                return SendToDaemon(event_type, data, true);
            }

            // Success, return.
            return true;
        }

        /// <summary>
        /// Generates an MD5 hash on the mission start time (in format yyyyMMddHHmmss) + mission name + . + mission map.
        /// This should only be called when the mission is first started.
        /// </summary>
        /// <param name="mission">Formatted string of mission_name.mission_map e.g. TestMission.Altis</param>
        /// <returns>Generated MD5 hash uniquely identifying this playthough.</returns>
        private static string GenerateMD5UniqueID(string mission)
        {
            string plaintext_identifier = DateTime.Now.ToString("yyyyMMddHHmmss") + mission;

            using (System.Security.Cryptography.MD5 md5 = System.Security.Cryptography.MD5.Create())
            {
                // Convert the pt ident to an array of bytes.
                byte[] inBytes = Encoding.ASCII.GetBytes(plaintext_identifier);

                // Compute the hash.
                byte[] hash = md5.ComputeHash(inBytes);

                // Convert the array of bytes to a string.
                StringBuilder hash_str = new StringBuilder();
                for (int i = 0; i < hash.Length; i++)
                {
                    hash_str.Append(hash[i].ToString("X2"));
                }

                return hash_str.ToString();
            }
        }

        private static void DebugToFile(string dt)
        {
#if DEBUG
            System.IO.StreamWriter file = new System.IO.StreamWriter("c:\\UOTEST.txt", true);
            file.WriteLine(dt);

            file.Close();
#endif
        }

        private static void HandleBrokenConnection()
        {
            try
            {
                pipe = new NamedPipeClientStream(".", "SIM" + unique_server_id, PipeDirection.Out, PipeOptions.WriteThrough);
                ss = new StreamWriter(pipe);
            }
            catch
            {
                // Not sure what could go wrong here, this is a potential memory leak waiting to happen, though.
            }

            // We need to try to connect to the pipe. the MAXIMUM wait time allowed is 50ms.
            try
            {
                pipe.Connect(100);
            }
            catch (TimeoutException)
            {
                // Pipe connection has timed out. Pass this along to the extension and make it wait.
            }
            catch (IOException)
            {
                // The server is connected to another client and the time-out period has expired.
                // Make the script wait and set the potential fuckup flag, if the fuckup flag has already been detected entirely exit the script.
            }
            catch
            {
                // Generic catchall. We REALLY don't want to crash the entire server.
            }
        }
    }
}
