using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    public class BansController : IController
    {
        public string Route { get; set; } = "bans";

        private readonly IBansService bansService;

        public BansController()
        {
            bansService = Store.GetService<BansService>();
        }

        public Task<PlayerBanDto> GetPlayerBanInfo(string playerName)
        {
            return bansService.GetPlayerBanInfo(playerName);
        }

        public Task<bool> BanPlayer(PlayerBanDto playerBanDto)
        {
            return bansService.BanPlayer(playerBanDto.UserName, playerBanDto.Issuer, playerBanDto.End, playerBanDto.Reason);
        }

        public Task<bool> UnbanPlayer(string playerName)
        {
            return bansService.UnbanPlayer(playerName);
        }

        public Task<bool> IsBanned(string playerName)
        {
            return bansService.IsBanned(playerName);
        }
    }
}
