using MySql.Data.MySqlClient;
using MDC.Common;
using MDC.Infrastructure.Generic;
using MDC.Infrastructure.Generic.Interfaces;
using System;
using System.Collections.Generic;

namespace MDC.Data
{
    public static class Database
    {
        private static IProperties properties;

        private static string dbAddress;
        private static string dbName;
        private static string dbUser;
        private static string dbPassword;

        internal static MySqlConnectionStringBuilder Builder { get; private set; }

        internal static DatabaseContext Context { get; private set; }

        public static bool IsInitialized => properties != null && Builder != null;
        
        public static void Initialize()
        {
            properties = new Properties(GetPropertiesPath());
            SetDefaultsForConfig();
            LoadProperties();

            InitMySqlConnectionStringBuilder();
        }

        public static void MakeContext()
        {
            Context = new DatabaseContext();
        }

        private static void InitMySqlConnectionStringBuilder()
        {
            Builder = new MySqlConnectionStringBuilder
            {
                Server = dbAddress,
                Database = dbName,
                UserID = dbUser,
                Password = dbPassword,
                PersistSecurityInfo = true,
                CharacterSet = "utf8"
            };
        }

        private static void SetDefaultsForConfig()
        {
            Dictionary<string, string> defaults = new Dictionary<string, string>()
            {
                { "address", string.Empty },
                { "name", "minepark" },
                { "user", "root" },
                { "password", "9999" }
            };
            properties.SetDefaults(defaults, true);
        }

        private static void LoadProperties()
        {
            dbAddress = properties.GetValue("address");
            dbName = properties.GetValue("name");
            dbUser = properties.GetValue("user");
            dbPassword = properties.GetValue("password");
        }

        private static string GetPropertiesPath()
        {
            if(!General.IsMainUnit)
            {
                return Environment.CurrentDirectory + @"\Data\Migrations\DB.properties";
            }

            return Defaults.PropertiesDatabaseFilename;
        }
    }
}
