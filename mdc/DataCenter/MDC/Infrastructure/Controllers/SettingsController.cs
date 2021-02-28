using MDC.Common.Network.HttpWeb;
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

        private readonly IUnitProvider unitProvider;
        
        private readonly IBankingService bankingService;

        public SettingsController()
        {
            unitProvider = Store.GetProvider<UnitProvider>();

            bankingService = Store.GetService<BankingService>();
        }

        public async Task UpgradeUnitId(string unitId, RequestContext requestContext)
        {
            unitProvider.SetCurrentUnitId(requestContext.AccessToken, unitId);
            await bankingService.InitializeUnitBalance(unitId);
        }
    }
}
