using MineParkProxy.Desktop.Configuration.Enums;

namespace MineParkProxy.Desktop.Configuration.Models
{
    public class Configuration
    {
        public ProxyMode Mode { get; set; }

        public string ListenerAddress { get; set; }

        public int ListenerPort { get; set; }

        public int ListenOnPort { get; set; }

        public int TargetPort { get; set; }
    }
}
