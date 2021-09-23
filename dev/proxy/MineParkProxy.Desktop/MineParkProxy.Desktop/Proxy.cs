using MineParkProxy.Desktop.Configuration;
using MineParkProxy.Desktop.Configuration.Enums;
using MineParkProxy.Desktop.Network;

using System;

namespace MineParkProxy.Desktop
{
    public static class Proxy
    {
        public static ConfigurationManager ConfigurationManager { get; private set; }

        public static TrafficManager TrafficManager { get; private set; }

        public static void StartApp()
        {
            ConfigurationManager = new ConfigurationManager();
            ConfigurationManager.LoadConfiguration();

            TrafficManager = new TrafficManager(ConfigurationManager.Configuration);

            SetTitle("MinePark Proxy App");

            StartProxy();
        }

        public static void SetTitle(string title)
        {
            Console.Title = title;
        }

        private static void StartProxy()
        {
            if (TrafficManager.Configuration.Mode == ProxyMode.Host)
            {
                Logger.Write("Starting proxy as Host...");

                MakeQuestion($"Are you ready to connect at {GetListenerAddress()}?");
            }
            else
            {
                Logger.Write("Starting proxy as Listener...");
            }

            TrafficManager.CreateBridge();

            WaitForInterrupt();
        }

        private static void MakeQuestion(string message)
        {
            Console.WriteLine(message);
            Console.ReadKey();
        }

        private static void WaitForInterrupt()
        {
            Logger.Write("Press Ctrl + C to exit the application.");

            ConsoleKeyInfo keyInfo;
            do
            {
                keyInfo = Console.ReadKey();
            }
            while (keyInfo.Key != ConsoleKey.C && 
                   keyInfo.Modifiers.HasFlag(ConsoleModifiers.Control));
        }

        private static string GetListenerAddress()
        {
            string address = ConfigurationManager.Configuration.ListenerAddress;
            int port = ConfigurationManager.Configuration.ListenerPort;

            return address + ':' + port;
        }
    }
}
