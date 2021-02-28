using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Threading.Tasks;

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

        public async Task UpgradeUnitId(string unitId)
        {
            contextProvider.SetCurrentUnitId(unitId);
            await bankingService.InitializeUnitBalance(unitId);
        }
    }
}
