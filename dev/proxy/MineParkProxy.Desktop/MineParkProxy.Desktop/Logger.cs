using System;
using System.IO;

namespace MineParkProxy.Desktop
{
    public static class Logger
    {
        public static void Write(string message)
        {
            string line = $"[{DateTime.Now}] {message}";

            Console.WriteLine(line);

            File.AppendAllText(Defaults.LogFile, Environment.NewLine + line);
        }
    }
}
