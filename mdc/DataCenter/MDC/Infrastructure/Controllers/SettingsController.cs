using MDC.Common;
using MDC.Common.Network.HttpWeb.Attributes;

using MDC.Data.Dtos;

using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;

using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    [WebRoute("settings")]
    public class SettingsController
    {
        private readonly ITokenService tokenService;

        public SettingsController(TokenService tokenService)
        {
            this.tokenService = tokenService;
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
