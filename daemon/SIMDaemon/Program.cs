using System;
using System.IO;
using System.IO.Pipes;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows;

using System.Net.Http;

namespace SIMDaemon
{
    class Program
    {
        const string unique_server_name = "SRV1";
        const string unique_pipe_name = "SIM" + unique_server_name;


        static NamedPipeServerStream pipeServer;
        static HttpClient inet = new HttpClient();

        static void Main(string[] args)
        {
            Console.WriteLine("[INFO/] Server Information Manager: Server Monitor Daemon");
            Console.WriteLine("[INFO/] Currently monitoring with: PIPE/simext."); // MEMORY/data-log, FILE/net-rpt
            Console.WriteLine("[INFO/] Alpha v0.0 - Created by Verox for UnitedOperations.net");
            Console.WriteLine();
            while (true)
            {
                if (pipeServer != null)
                {
                    Console.WriteLine(" disposing disconnected pipe.");
                    pipeServer.Dispose();
                }

                Console.Write("[INFO/PIPE/simext] Initializing new pipe...");
                try
                {
                    pipeServer = new NamedPipeServerStream(unique_pipe_name, PipeDirection.InOut);
                    Console.WriteLine(" success.");
                } 
                catch (Exception ex) // We're rethrowing this.
                {
                    // Let the user know something went wrong.
                    Console.WriteLine("\n[FATAL/PIPE/simext] Unable to initalize new pipe. This is unrecoverable.");
                    Console.WriteLine("[FATAL/PIPE/simext] " + ex.Message);

                    // Aaand rethrow. This kills the program.
                    throw;
                }
                #if DEBUG
                Console.WriteLine("[DEBUG/PIPE/simext] Initialzing new stream!"); // Debug output.
                #endif
                StreamReader sr = new StreamReader(pipeServer);

                // blocking-ly wait for a new connection to appear on the pipe.
                Console.WriteLine("[INFO/PIPE/simext] Waiting for connection on pipe.");
                pipeServer.WaitForConnection();
                Console.WriteLine("[INFO/PIPE/simext] Connection established on pipe!");

                // While we have a connection
                while (pipeServer.IsConnected) {
                    #if DEBUG           
                    Console.WriteLine("[DEBUG/PIPE/simext] TICK"); // Debug output.
                    #endif
                    string output = sr.ReadLine(); // Take data from the pipe...

                    // And if there's actually something to send, send it to the server.
                    if (output != null)
                        SendHTTP(output);

                    #if DEBUG
                    Console.WriteLine("[DEBUG/PIPE/simext] " + output); // Debug output.
                    #endif
                }

                Console.Write("[WARN/PIPE/simext] Pipe disconnected... "); // ACH, AIT ALL GON TITS UP, ERRR!               
            }
        }

        static async void SendHTTP(string data) {
            try
            {
                #if DEBUG
                Console.WriteLine("[DEBUG/PIPE/simext] Sending data.");
                #endif

                StringContent request_data = new StringContent(data, Encoding.UTF8, "application/json");
                var response = await inet.PostAsync("http://aar.unitedoperations.net/api/v1/data.php", request_data);

                #if DEBUG
                Console.WriteLine("[DEBUG/PIPE/simext] Data sent.");
                #endif
            } 
            catch (Exception ex)
            {
                Console.WriteLine("[EXCEPTION/PIPE/simext] An exception occured sending the data to the server.");
                Console.WriteLine("[EXCEPTION/PIPE/simext] " + ex.Message);
            }
        }
    }
}
