using MineParkProxy.Desktop.Configuration.Enums;

namespace MineParkProxy.Desktop
{
    public static class Defaults
    {
        public static readonly int AnalyticsUpdateIntervalMS = 2000;

        public static readonly string ConfigurationFile = "ProxySettings.json";

        public static readonly string LogFile = "ProxyLog.txt";

        public static readonly ProxyMode ProxyMode = ProxyMode.Host;

        public static readonly string Localhost = "127.0.0.1";

        public static readonly string ListenerAddress = "10.0.0.1";

        public static readonly int ListenerPort = 5000;

        public static readonly int ListenOnPort = 19100;

        public static readonly int HostPort = 19132;
    }
}
