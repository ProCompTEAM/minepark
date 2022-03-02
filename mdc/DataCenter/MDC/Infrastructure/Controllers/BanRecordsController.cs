using MDC.Common.Network.HttpWeb.Attributes;

using MDC.Data.Dtos;

using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;

using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    [WebRoute("bans")]
    public class BanRecordsController
    {
        private readonly IBanRecordsService banRecordsService;

        public BanRecordsController(BanRecordsService banRecordsService)
        {
            this.banRecordsService = banRecordsService;
        }

        public Task<UserBanRecordDto> GetUserBanRecord(string playerName)
        {
            return banRecordsService.GetUserBanRecordDto(playerName);
        }

        public Task<bool> BanUser(UserBanRecordDto playerBanDto)
        {
            return banRecordsService.BanUser(playerBanDto.UserName, playerBanDto.IssuerName, playerBanDto.ReleaseDate, playerBanDto.Reason);
        }

        public Task<bool> PardonUser(string userName)
        {
            return banRecordsService.PardonUser(userName);
        }

        public Task<bool> IsBanned(string userName)
        {
            return banRecordsService.IsBanned(userName);
        }
    }
}
