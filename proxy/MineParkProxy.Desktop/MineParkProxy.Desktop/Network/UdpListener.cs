using System.Net;
using System.Net.Sockets;

namespace MineParkProxy.Desktop.Network
{
    public class UdpListener
    {
        private readonly string listenerAddress;

        private readonly int listenerPort;

        private readonly int activePort;

        private TcpListener bridgeTcpListener;

        private TcpClient bridgeTcpClient;

        private UdpClient activePortUdpListener;

        private UdpClient activeUdpClient;

        public UdpListener()
        {
            listenerAddress = Proxy.ConfigurationManager.Configuration.ListenerAddress;
            listenerPort = Proxy.ConfigurationManager.Configuration.ListenerPort;

            activePort = Proxy.ConfigurationManager.Configuration.ListenOnPort;
        }

        public void WaitBridgeConnection()
        {
            bridgeTcpListener = new TcpListener(IPAddress.Parse(listenerAddress), listenerPort);
            bridgeTcpListener.Start();

            Threads.Start(ListenDataFromBridge);

            while (true)
            {
                bridgeTcpClient = bridgeTcpListener.AcceptTcpClient();
                Logger.Write($"Bridge created with {bridgeTcpClient.Client.RemoteEndPoint}");

                if(activePortUdpListener == null || !activePortUdpListener.Client.Connected)
                {
                    Threads.Start(ListenDgramsOnActivePort);
                }
            }
        }

        private void ListenDataFromBridge()
        {
            while(true)
            {
                if (bridgeTcpClient != null && bridgeTcpClient.Connected && bridgeTcpClient.Available > 0 && activeUdpClient != null)
                {
                    byte[] receivedData = new byte[bridgeTcpClient.Available];
                    bridgeTcpClient.Client.Receive(receivedData);
                    activeUdpClient.Send(receivedData, receivedData.Length);
                }
            }
        }

        private void ListenDgramsOnActivePort()
        {
            IPEndPoint remoteIpEndPoint = null;

            activePortUdpListener = new UdpClient(activePort);

            Logger.Write($"Listen data on {activePortUdpListener.Client.LocalEndPoint} > udp dgrams");

            while (true)
            {
                byte[] data = activePortUdpListener.Receive(ref remoteIpEndPoint);

                if (bridgeTcpClient != null && bridgeTcpClient.Connected)
                {
                    bridgeTcpClient.Client.Send(data);

                    InitializeNewUdpClient(remoteIpEndPoint.Address, remoteIpEndPoint.Port);

                    Analytics.AddReceivedBytesCount(data.Length);
                }
            }
        }

        private void InitializeNewUdpClient(IPAddress address, int port)
        {
            activeUdpClient = new UdpClient();
            activeUdpClient.Connect(address, port);
        }
    }
}
