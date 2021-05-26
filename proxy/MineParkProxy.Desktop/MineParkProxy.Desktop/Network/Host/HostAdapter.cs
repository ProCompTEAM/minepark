using MineParkProxy.Desktop.Network.Base;

namespace MineParkProxy.Desktop.Network.Host
{
    public class HostAdapter : BaseAdapter
    {
        private readonly TrafficManager trafficManager;

        public HostAdapter(TrafficManager trafficManager) : base(trafficManager.Configuration)
        {
            this.trafficManager = trafficManager;
        }
    }
}
