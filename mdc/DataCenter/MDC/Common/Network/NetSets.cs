﻿using MDC.Common.Network.HttpWeb;

namespace MDC.Common.Network
{
    public static class NetSets
    {
        private static WebServer webServer;

        public static void Initialize()
        {
            CreateWebMDC();
        }

        private static void CreateWebMDC()
        {
            string address = General.Properties.WebListenerAddress;
            int port = General.Properties.WebListenerPort;

            webServer = new WebServer(address, port);

            webServer.Listen().Wait();
        }
    }
}
