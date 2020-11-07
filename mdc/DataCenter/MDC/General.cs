using MDC.Common;
using MDC.Common.Mapping;
using MDC.Common.Network;
using MDC.Data;
using MDC.Infrastructure;
using MDC.Infrastructure.Generic;
using MDC.Infrastructure.Generic.Interfaces;
using System;
using System.Reflection;

namespace MDC
{
    public static class General
    {
        private static ILogger logger;

        private static ICrashLogger crashLogger;

        public static bool IsMainUnit { get; private set; } = false;

        public static MDCProperties Properties { get; set; }

        public static void LoadAll()
        {
            SetTitle("Starting...");

            IsMainUnit = true;

            InitializeAll();
        }

        public static void Log(string message, params object[] objs)
        {
            logger.Info(string.Format(message, objs), Defaults.LoggerMDCPrefix, ConsoleColor.White);
        }

        public static void Error(string message, params object[] objs)
        {
            logger.Info(string.Format(message, objs), Defaults.LoggerErrorPrefix, ConsoleColor.Red);
        }

        public static void Crash(string description, string[] traces)
        {
            crashLogger.Crash(description, traces);
        }

        public static void SetTitle(string titleMessage)
        {
            Console.Title = $"{ProductName} {Version}: {titleMessage}";
        }

        public static string Version => Assembly.GetExecutingAssembly().GetName().Version.ToString();

        public static string ProductName => Assembly.GetExecutingAssembly().GetName().Name;

        private static void InitializeAll()
        {
            CommonMapper.Initialize();

            Store.InitializeAll();

            Properties = new MDCProperties();
            logger = new Logger();
            crashLogger = new CrashLogger();

            Log("Loading database...");
            Database.Initialize();
            Database.MakeContext();

            Log("Loading web services...");
            NetSets.Initialize();
        }
    }
}
