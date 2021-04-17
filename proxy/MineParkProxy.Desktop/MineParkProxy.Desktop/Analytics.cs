using System.Threading;

namespace MineParkProxy.Desktop
{
    public static class Analytics
    {
        public static long ReceivedBytesCounter { get; set; } = 0;

        public static void StartAnalytics()
        {
            Threads.Start(UpdateTitleWithInterval);
        }

        private static void UpdateTitleWithInterval()
        {
            while (ReceivedBytesCounter > 0)
            {
                Proxy.SetTitle($"Received data {ReceivedBytesCounter} bytes");

                Thread.Sleep(Defaults.AnalyticsUpdateIntervalMS);
            }
        }
    }
}