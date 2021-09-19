using MineParkProxy.Desktop.Configuration.Enums;
using MineParkProxy.Desktop.Network.Bridge;
using MineParkProxy.Desktop.Network.Host;

using ConfigurationModel = MineParkProxy.Desktop.Configuration.Models.Configuration;

namespace MineParkProxy.Desktop.Network
{
    public class TrafficManager
    {
        public ConfigurationModel Configuration { get; private set; }

        private readonly ServerBridgeAdapter serverBridgeAdapter;

        private readonly RemoteBridgeAdapter remoteBridgeAdapter;

        private readonly HostAdapter hostAdapter;

        private readonly UdpListener udpListener;

        public TrafficManager(ConfigurationModel configuration)
        {
            Configuration = configuration;

            serverBridgeAdapter = new ServerBridgeAdapter(this);
            remoteBridgeAdapter = new RemoteBridgeAdapter(this);

            hostAdapter = new HostAdapter(this);
            udpListener = new UdpListener(this);
        }

        public void CreateBridge()
        {
            if(Configuration.Mode == ProxyMode.Host)
            {
                Threads.Start(remoteBridgeAdapter.CreateBridgeConnection);
            }
            else
            {
                Threads.Start(serverBridgeAdapter.WaitBridgeConnection);
                Threads.Start(serverBridgeAdapter.ListenDataFromBridge);
            }
        }

        public void BroadcastDataUdp(byte[] data)
        {
        }
    }
}
