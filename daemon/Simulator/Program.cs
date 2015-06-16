using System;
using System.Collections.Generic;
using System.IO.Pipes;
using System.IO;
using System.Linq;
using System.Text;
using System.Runtime.InteropServices;
using System.Threading.Tasks;

namespace Simulator
{
    class Program
    {
        static void Main(string[] args)
        {
            while (true)
            {
                switch (Console.ReadLine())
                {
                    case "SPAM":
                        Console.WriteLine("SPAMMING!");
                        DataSpammer.CreateSpammedData(400, 1800);
                        Console.WriteLine("SPAMED!");
                        break;
                    case "S":
                        Simulator.RVExtension("S21234");
                        break;
                    case "D":
                        Simulator.RVExtension(SendRandomData());
                        break;
                    case "E":
                        Simulator.RVExtension("E");
                        break;
                    case "SEND":
                        Simulator.RVExtension("S21234");
                        Simulator.RVExtension(SendRandomData());
                        Simulator.RVExtension("E");
                        break;
                    default:
                        Console.WriteLine("Unknown command.");
                        break;
                }
            }
        }

        static string SendRandomData()
        {
            return "Data.";
        }
    }

    class DataSpammer
    {
        const string json_header = "{\"time\":\"";
        const string json_header2 = "\",\"units\": [";
        const string json_footer = "]}";


        public static void CreateSpammedData(int units, int timepoints)
        {
            // Create the random unit IDs
            string [] ids = new string[units];
            Random rd = new Random();

            

            for (int i = 0; i < units; i++) {
                ids[i] = rd.Next(10000000, 99999999).ToString() + rd.Next(10000000, 99999999).ToString() + rd.Next(10000000, 99999999).ToString() + rd.Next(10000000, 99999999).ToString();
            }

            for (int i = 0; i < timepoints; i++)
            {
                StringBuilder datapoint = new StringBuilder();
                datapoint.Append(json_header + (i * 10) + json_header2);

                foreach (string id in ids) {
                    datapoint.Append("{\"nid\": \"" + rd.Next(1000,10000).ToString() + "\",\"uid\": \"" + id + "\",\"pos\": \"[" + rd.Next(0,10000).ToString() + ","+ rd.Next(0,10000).ToString() + ","+ rd.Next(0,10000).ToString() + "]\", \"fac\": \"WEST\", \"dir\": \"" + rd.Next(0,360) + "\"},");
                }

                datapoint.Append(json_footer);
                File.AppendAllText("C:\\tester.txt", datapoint.ToString());
            }
        }
    }

    class Simulator
    {

        const string json_header = "{\"time\":\"";
        const string json_header2 = "\",\"units\": [";
        const string json_footer = "]}\n"; // EOL required for StreamReader to actually fucking read.

        public static StringBuilder datastring = new StringBuilder();

        static NamedPipeClientStream pipe = new NamedPipeClientStream(".", "SIMSRV1", PipeDirection.Out, PipeOptions.WriteThrough);
        static StreamWriter ss = new StreamWriter(pipe);

        public static void RVExtension(string function)
        {
            StringBuilder output = new StringBuilder();
            int outputSize;

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

        // Put this in it's own thread. Should improve speedyness.
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
    }
}

