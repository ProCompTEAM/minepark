using MDC.Common;
using MDC.Common.Network.HttpWeb;
using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    public class SettingsController : IController
    {
        public string Route { get; set; } = "settings";

        private readonly IUnitProvider unitProvider;
        
        private readonly IBankingService bankingService;

        private readonly ITokenService tokenService;

        public SettingsController()
        {
            unitProvider = Store.GetProvider<UnitProvider>();

            bankingService = Store.GetService<BankingService>();

            tokenService = Store.GetService<TokenService>();
        }

        public int GetProtocolVersion()
        {
            return Protocol.Version;
        }

        public async Task UpgradeUnitId(string unitId, RequestContext requestContext)
        {
            unitProvider.SetCurrentUnitId(requestContext.AccessToken, unitId);
            await bankingService.InitializeUnitBalance(unitId);
        }

        public async Task<string> GenerateToken(string tag)
        {
            return await tokenService.GenerateToken(tag);
        }

        public async Task RemoveToken(string token)
        {
            await tokenService.RemoveToken(token);
        }

        public List<CredentialsDto> GetTokens(RequestContext requestContext)
        {
            return tokenService.GetTokens();
        }
    }
}
