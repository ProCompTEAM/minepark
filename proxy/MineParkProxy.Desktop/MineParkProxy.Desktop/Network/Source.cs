using System.Net;
using System.Net.Sockets;

namespace MineParkProxy.Desktop.Network
{
    public class Source
    {
        private readonly string bridgeAddress;

        private readonly int bridgePort;

        private readonly int targetPort;

        private TcpClient bridgeTcpClient;

        private UdpClient targetUdpClient;

        public Source()
        {
            bridgeAddress = Proxy.ConfigurationManager.Configuration.ListenerAddress;
            bridgePort = Proxy.ConfigurationManager.Configuration.ListenerPort;

            targetPort = Proxy.ConfigurationManager.Configuration.TargetPort;
        }

        public void CreateBridge()
        {
            bridgeTcpClient = new TcpClient(bridgeAddress, bridgePort);

            Logger.Write($"Bridge created with {bridgeAddress}:{bridgePort}.");

            while (bridgeTcpClient.Connected)
            {
                if(bridgeTcpClient.Available > 0)
                {
                    byte[] data = new byte[bridgeTcpClient.Available];
                    bridgeTcpClient.Client.Receive(data);

                    CreateUdpRequest(data);

                    Analytics.AddReceivedBytesCount(data.Length);
                }
            }
        }

        private void CreateUdpRequest(byte[] data)
        {
            if(targetUdpClient != null && targetUdpClient.Client.Connected)
            {
                targetUdpClient.Send(data, data.Length);
            }
            else
            {
                Threads.Start(ConnectTarget);
            }
        }

        private void ConnectTarget()
        {
            IPEndPoint remoteIpEndPoint = new IPEndPoint(IPAddress.Any, 0);
            targetUdpClient = new UdpClient(Defaults.Localhost, targetPort);

            while (targetUdpClient.Client.Connected)
            {
                byte[] receivedData = targetUdpClient.Receive(ref remoteIpEndPoint);
                bridgeTcpClient.Client.Send(receivedData);
            }
        }
    }
}
