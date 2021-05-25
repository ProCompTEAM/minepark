using ConfigurationModel = MineParkProxy.Desktop.Configuration.Models.Configuration;

namespace MineParkProxy.Desktop.Network.Base
{
    public abstract class BaseAdapter
    {
        public bool Enabled { get; private set; }

        public ConfigurationModel Configuration { get; private set; }

        public BaseAdapter(ConfigurationModel configuration)
        {
            Enabled = false;

            Configuration = configuration;
        }

        public void Enable()
        {
            Enabled = true;
        }

        public void Disable()
        {
            Enabled = false;
        }
    }
}
