using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;

namespace MDC.Infrastructure.Controllers
{
    public class SettingsController : IController
    {
        public string Route { get; set; } = "settings";

        private readonly IContextProvider contextProvider;

        public SettingsController()
        {
            contextProvider = Store.GetProvider<ContextProvider>();
        }

        public void UpgradeUnitId(string unitId)
        {
            contextProvider.SetCurrentUnitId(unitId);
        }
    }
}
