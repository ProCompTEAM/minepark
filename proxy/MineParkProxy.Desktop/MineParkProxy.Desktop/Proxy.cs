using MineParkProxy.Desktop.Configuration;
using MineParkProxy.Desktop.Configuration.Enums;
using MineParkProxy.Desktop.Network;

using System;

namespace MineParkProxy.Desktop
{
    public static class Proxy
    {
        public static ConfigurationManager ConfigurationManager { get; private set; }

        private static UdpListener listener;
        private static Source source;

        public static void StartApp()
        {
            ConfigurationManager = new ConfigurationManager();
            ConfigurationManager.LoadConfiguration();

            listener = new UdpListener();
            source = new Source();

            SetTitle("MinePark Proxy App");

            StartProxy();
        }

        public static void SetTitle(string title)
        {
            Console.Title = title;
        }

        private static void StartProxy()
        {
            ProxyMode mode = ConfigurationManager.Configuration.Mode;
            string address = GetListenerAddress();

            if (mode == ProxyMode.Source)
            {
                Logger.Write("Starting proxy as Client-Source...");

                MakeQuestion($"Are you ready to connect at {address}?");

                source.CreateBridge();
            }
            else
            {
                Logger.Write("Starting proxy as Server-Listener...");

                listener.WaitBridgeConnection();
            }
        }

        private static void MakeQuestion(string message)
        {
            Console.WriteLine(message);
            Console.ReadKey();
        }

        private static string GetListenerAddress()
        {
            string address = ConfigurationManager.Configuration.ListenerAddress;
            int port = ConfigurationManager.Configuration.ListenerPort;

            return address + ':' + port;
        }
    }
}
