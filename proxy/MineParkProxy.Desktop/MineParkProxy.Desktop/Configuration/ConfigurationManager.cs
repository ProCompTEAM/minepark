using ConfigurationModel = MineParkProxy.Desktop.Configuration.Models.Configuration;

using System.IO;
using System.Text.Json;

namespace MineParkProxy.Desktop.Configuration
{
    public class ConfigurationManager
    {
        public ConfigurationModel Configuration { get; private set; }

        public void LoadConfiguration()
        {
            if (File.Exists(Defaults.ConfigurationFile))
            {
                Configuration = LoadConfigurationFromFile();
            } 
            else
            {
                Configuration = CreateConfigurationFile();
            }
        }

        private ConfigurationModel LoadConfigurationFromFile()
        {
            string json = File.ReadAllText(Defaults.ConfigurationFile);
            return JsonSerializer.Deserialize<ConfigurationModel>(json);
        }

        private ConfigurationModel CreateConfigurationFile()
        {
            ConfigurationModel configuration = GetNewConfigurationTemplate();
            JsonSerializerOptions options = GetJsonSerializerOptions();

            string json = JsonSerializer.Serialize(configuration, options);
            File.WriteAllText(Defaults.ConfigurationFile, json);

            return configuration;
        }

        private ConfigurationModel GetNewConfigurationTemplate()
        {
            return new ConfigurationModel
            {
                Mode = Defaults.ProxyMode,
                ListenerAddress = Defaults.ListenerAddress,
                ListenerPort = Defaults.ListenerPort,
                ListenOnPort = Defaults.ListenOnPort,
                HostPort = Defaults.HostPort
            };
        }

        private JsonSerializerOptions GetJsonSerializerOptions()
        {
            return new JsonSerializerOptions
            {
                WriteIndented = true
            };
        }
    }
}