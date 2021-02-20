using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;

namespace MDC.Infrastructure.Controllers
{
    public class SettingsController : IController
    {
        public string Route { get; set; } = "settings";

        private readonly IContextProvider contextProvider;
        
        private readonly IBankingService bankingService;

        public SettingsController()
        {
            contextProvider = Store.GetProvider<ContextProvider>();

            bankingService = Store.GetService<BankingService>();
        }

        public void UpgradeUnitId(string unitId)
        {
            contextProvider.SetCurrentUnitId(unitId);
            bankingService.InitializeUnitBalance(unitId);
        }
    }
}
