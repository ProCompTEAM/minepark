using System.Collections.Generic;

namespace MDC.Infrastructure.Generic.Interfaces
{
    public interface IProperties
    {
        string GetValue(string property);

        bool Exists(string key);

        void SetValue(string property, string value);

        void Clear();

        void Save();

        void SetDefaults(Dictionary<string, string> defaults, bool saveToFile = false);
    }
}
