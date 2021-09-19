using MineParkProxy.Desktop.Network.Base;

using System.Net.Sockets;

namespace MineParkProxy.Desktop.Network.Bridge
{
    public class RemoteBridgeAdapter : BaseAdapter
    {
        private readonly TrafficManager trafficManager;

        private readonly string bridgeAddress;

        private readonly int bridgePort;

        private TcpClient bridgeTcpClient;

        public RemoteBridgeAdapter(TrafficManager trafficManager) : base(trafficManager.Configuration)
        {
            this.trafficManager = trafficManager;

            bridgeAddress = Configuration.ListenerAddress;
            bridgePort = Configuration.ListenerPort;
        }

        public void CreateBridgeConnection()
        {
            Enable();

            bridgeTcpClient = new TcpClient(bridgeAddress, bridgePort);

            Logger.Write($"Bridge created with {bridgeAddress}:{bridgePort}.");

            while (Enabled && bridgeTcpClient.Connected)
            {
                if (bridgeTcpClient.Available > 0)
                {
                    byte[] data = new byte[bridgeTcpClient.Available];
                    bridgeTcpClient.Client.Receive(data);

                    trafficManager.BroadcastDataUdp(data);

                    Analytics.AddReceivedBytesCount(data.Length);
                }
            }
        }
    }
}
