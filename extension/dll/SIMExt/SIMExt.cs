// http://maca134.co.uk/tutorial/write-an-arma-extension-in-c-sharp-dot-net/ - Thanks!
using RGiesecke.DllExport; //https://sites.google.com/site/robertgiesecke/Home/uploads/unmanagedexports

using System;
using System.Collections.Generic;
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
        // Constant JSON information.
        const string json_header = "{\"time\":\"";
        const string json_header2 = "\",\"units\": [";
        const string json_footer = "]}\n"; // EOL required for StreamReader to actually fucking read.

        // Strinbuilder for fun.
        static StringBuilder datastring = new StringBuilder();

        static NamedPipeClientStream pipe = new NamedPipeClientStream(".", "SIMSRV1", PipeDirection.Out, PipeOptions.WriteThrough);
        static StreamWriter ss = new StreamWriter(pipe);

        [DllExport("_RVExtension@12", CallingConvention = System.Runtime.InteropServices.CallingConvention.Winapi)]
        public static void RVExtension(StringBuilder output, int outputSize, [MarshalAs(UnmanagedType.LPStr)] string function)
        {
            if (function[0] == 'S')
            {
                if (!pipe.IsConnected) { HandleBrokenConnection(); }

                datastring.Append(json_header + function.Substring(1) + json_header2);
            }
            else if (function[0] == 'E')
            {
                // Append the final closing brackets for the json.
                datastring.Append(json_footer);

                if (!SendToDaemon())
                {
                    // Something went wrong.
                    output.Append("PIPE");
                }

                // All done, wipe it away.
                datastring.Clear();
            }
            else
            {
                datastring.Append('{' + function + '}');
            }

            outputSize = output.Length;
        }

        // Put this in it's own thread. Should improve speedyness. Or use FlushAsync.
        private static bool SendToDaemon(bool retry = false)
        {
            // Try to write and flush the pipe.
            try
            {
                ss.Write(datastring);
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
                SendToDaemon(true);
            }
            finally
            {
                datastring.Clear();
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
                // Not sure what could go wrong here, this is a potential memory leak waiting to happen.
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

        /*public static bool ProcessData(string data) 
        {


            // Check if the previous operation has timed out.
            bool timeout = false;

            // If arma is requesting we start logging new data...
            if (data[0] == 'S')
            {
                // check we're ready to start
                if (programState == State.READY) // Good to go, start.
                {
                    programState = State.BUSY; // Set the program to busy state.
                    // Set the json start time.
                    // Start the timer.

                    return true;
                }
                else
                {
                    return false;
                }
            }
            else if (data[0] == 'E')
            {
                if (programState != State.BUSY)
                {
                    // Ah something fucked up. Discard this loop.
                    ResetExtension();
                    return true;
                }
                else
                {
                    // Send the shit to the daemon.
                }
            }
            else
            {
                if (programState != State.BUSY)
                {
                    // Ah something fucked up. Tell the program to abort.
                    programState = State.ABORT;
                    return false;
                }
                else
                {
                    datastring.Append('{' + data + '}');
                    return true;
                }
            }


            // Everything went as expected.
            return true;
        }

        private static void SendToDaemon()
        {

        }

        public static void ResetExtension() 
        {
            // Clear the timer.
            datastring.Clear();
            programState = State.READY;
        }

        public static string GetError()
        {
            if (programState != State.READY)
            {
                switch (programState)
                {
                    case State.BUSY:
                        return "BUSY WITH CURRENT DATA";
                    case State.GENERIC:
                        return "UNKNOWN ERROR";
                    case State.READY:
                        return "WAITING FOR NEW DATA";
                    case State.LOAD:
                        return "EXCESSIVE SERVER LOAD DETECTED! INCREASING POLL INTERVAL.";
                    case State.ABORT:
                        return "ABORT";
                    case State.PIPEERROR:
                        return "PIPE ERROR, IS THE DAEMON ALIVE?";
                    default:
                        return "WTF STATE DETECTED";
                }
            }

            return null;
        }*/
    }
}
