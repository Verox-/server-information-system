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
        
        const string json_data_header = "\"time\":{0},\"units\": [";
        const string json_data_footer = "]";

        static bool ready = true;
        static string mission_playthrough_hash;
        static string last_fps = "50";

        // Strinbuilder for fun.
        static StringBuilder datastring = new StringBuilder();

        static NamedPipeClientStream pipe = new NamedPipeClientStream(".", "SIMSRV1", PipeDirection.Out, PipeOptions.WriteThrough);
        static StreamWriter ss = new StreamWriter(pipe);

        [DllExport("_RVExtension@12", CallingConvention = System.Runtime.InteropServices.CallingConvention.Winapi)]
        public static void RVExtension(StringBuilder output, int outputSize, [MarshalAs(UnmanagedType.LPStr)] string function)
        {
#if DEBUG
            DebugToFile(function);
#endif

            if (function[0] == 'S') // Started a unit report.
            {
                if (!pipe.IsConnected) { HandleBrokenConnection(); } // Check the pipe is still connected, if it isn't try to reconnect.

                if (!ready)
                {
                    datastring.Clear();
                }

                datastring.AppendFormat(json_data_header, function.Substring(1));

                ready = false;
            }
            else if (function[0] == 'E') // Ended a unit report.
            {
                // If the mission start isn't set then this loop is broken. Exit immediatley.
                if (mission_playthrough_hash == null)
                    return;

                // Get rid of the last comma to make it valid json.
                if (datastring[datastring.Length - 1] == ',')
                {
                    datastring.Remove(datastring.Length - 1, 1);
                }
                
                // Append the final closing bracket for the json.
                datastring.Append(json_data_footer);
                
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
                datastring.Append('{');
                datastring.Append(function);
                datastring.Append("},");
            }

            outputSize = output.Length;
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

            return null;
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
