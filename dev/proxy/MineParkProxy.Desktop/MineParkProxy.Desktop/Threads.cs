using System.Threading;

namespace MineParkProxy.Desktop
{
    public static class Threads
    {
        public static void Start(ThreadStart threadStart)
        {
            Thread thread = new Thread(threadStart);
            thread.Start();
        }

        public static void Start(ParameterizedThreadStart parameterizedThreadStart)
        {
            Thread thread = new Thread(parameterizedThreadStart);
            thread.Start();
        }
    }
}
