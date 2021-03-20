using System.Collections.Generic;
using System.IO;
using MDC.Infrastructure.Generic.Interfaces;

namespace MDC.Infrastructure.Generic
{
    public class Properties : IProperties
    {
        public const string SwitchEnabled = "on";
        public const string SwitchDisabled = "off";

        public string Path { get; protected set; }

        protected Dictionary<string, string> properties;

        public Properties(string path)
        {
            Path = path;

            properties = new Dictionary<string, string>();

            if (File.Exists(path))
            {
                LoadFromFile();
            }
        }

        public string GetValue(string property)
        {
            if (Exists(property))
            {
                return properties[property];
            }
            
            return null;
        }

        public bool Exists(string key)
        {
            return properties.ContainsKey(key);
        }

        public void SetValue(string property, string value)
        {
            properties[property] = value;
        }

        public void Clear()
        {
            properties.Clear();
        }

        public void Save()
        {
            List<string> list = new List<string>();

            foreach (string key in properties.Keys)
            {
                list.Add(key + "=" + properties[key]);
            }

            File.WriteAllLines(Path, list);
        }

        public void SetDefaults(Dictionary<string, string> defaults, bool saveToFile = false)
        {
            foreach(string key in defaults.Keys)
            {
                if(!Exists(key))
                {
                    properties[key] = defaults[key];
                }
            }

            if(saveToFile)
            {
                Save();
            }
        }

        private void LoadFromFile()
        {
            string[] lines = File.ReadAllLines(Path);

            foreach (string line in lines)
            {
                if (line.Length == 0 || line[0] == '#')
                {
                    continue;
                }

                string key = line.Split('=')[0];
                string val = line.Split('=')[1];
                properties[key] = val;
            }
        }
    }
}
