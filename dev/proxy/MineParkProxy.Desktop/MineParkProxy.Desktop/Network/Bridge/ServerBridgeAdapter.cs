using MineParkProxy.Desktop.Network.Base;

using System.Net;
using System.Net.Sockets;

namespace MineParkProxy.Desktop.Network.Bridge
{
    public class ServerBridgeAdapter : BaseAdapter
    {
        private readonly TrafficManager trafficManager;

        private readonly string listenerAddress;

        private readonly int listenerPort;

        private TcpListener bridgeTcpListener;

        private TcpClient bridgeTcpClient;

        public ServerBridgeAdapter(TrafficManager trafficManager) : base(trafficManager.Configuration)
        {
            this.trafficManager = trafficManager;

            listenerAddress = Proxy.ConfigurationManager.Configuration.ListenerAddress;
            listenerPort = Proxy.ConfigurationManager.Configuration.ListenerPort;
        }

        public void WaitBridgeConnection()
        {
            Enable();

            bridgeTcpListener = new TcpListener(IPAddress.Parse(listenerAddress), listenerPort);
            bridgeTcpListener.Start();

            while (Enabled)
            {
                bridgeTcpClient = bridgeTcpListener.AcceptTcpClient();
                Logger.Write($"Bridge created with {bridgeTcpClient.Client.RemoteEndPoint}");
            }
        }

        public void ListenDataFromBridge()
        {
            while (Enabled)
            {
                if (bridgeTcpClient != null && bridgeTcpClient.Connected && bridgeTcpClient.Available > 0)
                {
                    byte[] receivedData = new byte[bridgeTcpClient.Available];
                    bridgeTcpClient.Client.Receive(receivedData);
                    trafficManager.BroadcastDataUdp(receivedData);
                }
            }
        }
    }
}
