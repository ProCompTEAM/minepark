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

            while(true)
            {
                bridgeTcpClient = bridgeTcpListener.AcceptTcpClient();
                Logger.Write($"Bridge created with {bridgeTcpClient.Client.RemoteEndPoint}");
            }
        }

        public void ListenDgramsOnActivePort()
        {
            IPEndPoint remoteIpEndPoint = new IPEndPoint(IPAddress.Any, 0);
            activePortUdpListener = new UdpClient(activePort);

            while(true)
            {
                if(activePortUdpListener.Available > 0 && bridgeTcpClient != null && bridgeTcpClient.Connected)
                {
                    byte[] data = activePortUdpListener.Receive(ref remoteIpEndPoint);
                    bridgeTcpClient.Client.Send(data);

                    Analytics.ReceivedBytesCounter += data.Length;
                }
            }
        }
    }
}
