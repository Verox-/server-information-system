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
        static NamedPipeServerStream pipeServer;
        static HttpClient inet = new HttpClient();

        static void Main(string[] args)
        {
            while (true)
            {
                if (pipeServer != null)
                {
                    Console.WriteLine("Dispose the old pipe.");
                    pipeServer.Dispose();
                }

                Console.WriteLine("Brand new pipe!"); // Debug output.
                pipeServer = new NamedPipeServerStream("SIMSRV1", PipeDirection.InOut);
                Console.WriteLine("Fresh new stream!"); // Debug output.
                StreamReader sr = new StreamReader(pipeServer);
                pipeServer.WaitForConnection();
                Console.WriteLine("Hello, connection!"); // Debug output.

                // While we have a connection
                while (pipeServer.IsConnected) {
                    Console.WriteLine("TICK"); // Debug output.
                    string output = sr.ReadLine(); // Yum the line from the PIPE.

                    if (output != null)
                        SendHTTP(output);

                    Console.WriteLine(output); // Debug output.
                }

                Console.WriteLine("ACH ERR!"); // ACH, AIT ALL GON TITS UP, ERRR!               
            }
        }

        static async void SendHTTP(string data) {
            Console.WriteLine("Sendin' doe");
            StringContent request_data = new StringContent(data, Encoding.UTF8, "application/json");
            var response = await inet.PostAsync("http://aar.unitedoperations.net/api/v1/data.php", request_data);

            Console.WriteLine("Sent doe");
        }
    }
}
