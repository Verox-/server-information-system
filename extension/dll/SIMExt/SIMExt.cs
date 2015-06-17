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
        // Constant JSON structure.
        
        const string json_data_header = "\"time\":\"{0}\",\"units\": [";
        const string json_data_footer = "]";

        static bool ready = true;

        // Strinbuilder for fun.
        static StringBuilder datastring = new StringBuilder();

        static NamedPipeClientStream pipe = new NamedPipeClientStream(".", "SIMSRV1", PipeDirection.Out, PipeOptions.WriteThrough);
        static StreamWriter ss = new StreamWriter(pipe);

        [DllExport("_RVExtension@12", CallingConvention = System.Runtime.InteropServices.CallingConvention.Winapi)]
        public static void RVExtension(StringBuilder output, int outputSize, [MarshalAs(UnmanagedType.LPStr)] string function)
        {
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
                // Get rid of the last comma to make it valid json.
                datastring.Remove(datastring.Length - 2, 1);

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
                SendToDaemon("start_mission", function.Substring(1));
            }
            else if (function[0] == 'F') // Finished the current mission.
            {
                if (!SendToDaemon("end_mission", "time:" + function.Substring(1)))
                {
                    // Something went wrong.
                    output.Append("PIPE");
                }
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
            string data_to_send = "{\"event\":\"" + event_type + "\",\"data\":{" + data + "}\n";

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
                SendToDaemon(event_type, data, true);
            }

            // Success, return.
            return true;
        }

        private static void HandleBrokenConnection()
        {
            try
            {
                pipe = new NamedPipeClientStream(".", "SIMSRV1", PipeDirection.Out, PipeOptions.WriteThrough);
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
