using MDC.Common;
using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    public class SettingsController : IController
    {
        public string Route { get; set; } = "settings";

        private readonly ITokenService tokenService;

        public SettingsController()
        {
            tokenService = Store.GetService<TokenService>();
        }

        public int GetProtocolVersion()
        {
            return Protocol.Version;
        }

        public async Task<string> GenerateToken(string tag)
        {
            return await tokenService.GenerateToken(tag);
        }

        public async Task RemoveToken(string token)
        {
            await tokenService.RemoveToken(token);
        }

        public List<CredentialsDto> GetTokens()
        {
            return tokenService.GetTokens();
        }
    }
}
