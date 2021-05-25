using MineParkProxy.Desktop.Network.Base;

namespace MineParkProxy.Desktop.Network.Host
{
    public class UdpListener : BaseAdapter
    {
        private readonly TrafficManager trafficManager;

        public UdpListener(TrafficManager trafficManager) : base(trafficManager.Configuration)
        {
            this.trafficManager = trafficManager;
        }
    }
}
