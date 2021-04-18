namespace MineParkProxy.Desktop
{
    public static class Analytics
    {
        public static long ReceivedBytesCounter { get; private set; } = 0;

        public static void AddReceivedBytesCount(long bytes)
        {
            ReceivedBytesCounter += bytes;
            Proxy.SetTitle($"Received data {ReceivedBytesCounter} bytes");
        }
    }
}