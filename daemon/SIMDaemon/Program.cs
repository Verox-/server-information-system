// /* LICENCE
// Daemon for data.
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

using System;
using System.IO;
using System.IO.Pipes;
using System.Collections.Generic;
using System.Linq;
using System.Reflection;
using System.Text;
using System.Threading.Tasks;
using System.Windows;

using System.Runtime.InteropServices;
using System.Net.Http;
using System.Xml.Serialization;

namespace SIMDaemon
{
    public struct DaemonConfiguration
    {
        public String ApiEndpoint;
        public String ServerName;
    }
    class Program
    {
        #region NaitiveImports
        [DllImport("kernel32.dll")]
        static extern IntPtr GetConsoleWindow();

        [DllImport("user32.dll")]
        static extern bool ShowWindow(IntPtr hWnd, int nCmdShow);

        const int SW_HIDE = 0;
        const int SW_SHOW = 5;
        #endregion

        private static string _uniquePipeName;
        private static string UniquePipeName
        {
            get
            {
                if (String.IsNullOrEmpty(_uniquePipeName))
                    _uniquePipeName = "SIM" + Config.ServerName;
                return _uniquePipeName;
            }
        }
        public static String ConfigurationPath
        {
            get { return "config.xml"; }
        }
        static NamedPipeServerStream pipeServer;
        static HttpClient inet = new HttpClient();
        public static DaemonConfiguration Config;
        static void Main(string[] args)
        {
            Config = LoadConfiguration();
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
                    pipeServer = new NamedPipeServerStream(UniquePipeName, PipeDirection.InOut);
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

                #if !DEBUG
                // Hide the window, we don't need it show it anymore unless something breaks.
                ShowWindow(false);
                #endif

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

                #if !DEBUG
                // Pipe disconnected, show the window.
                ShowWindow(true);
                #endif

                Console.Write("[WARN/PIPE/simext] Pipe disconnected... "); // ACH, AIT ALL GON TITS UP, ERRR!
            }
        }

        private static DaemonConfiguration LoadConfiguration()
        {
            var serializer = new XmlSerializer(typeof(DaemonConfiguration));
            if (!File.Exists(ConfigurationPath))
            {
                Console.WriteLine("[WARN] No configuration found, a default generated.");
                var config = new DaemonConfiguration()
                {
                    ApiEndpoint = "http://aar.unitedoperations.net/api/v1/data.php",
                    ServerName = "SRV1"
                };
                using (var stream = File.OpenWrite(ConfigurationPath))
                {
                    serializer.Serialize(stream, config);
                }
                return config;
            }
            using (var stream = File.OpenRead(ConfigurationPath))
            {
                return (DaemonConfiguration) serializer.Deserialize(stream);
            }
        }

        static void ShowWindow(bool show)
        {
            var handle = GetConsoleWindow();

            if (show)
            {
                // Show
                ShowWindow(handle, SW_SHOW);
            }
            else
            {
                // Hide
                ShowWindow(handle, SW_HIDE);
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
