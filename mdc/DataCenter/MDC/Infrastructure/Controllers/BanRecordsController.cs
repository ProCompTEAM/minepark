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
    public class BanRecordsController : IController
    {
        public string Route { get; set; } = "bans";

        private readonly IBanRecordsService banRecordsService;

        public BanRecordsController()
        {
            banRecordsService = Store.GetService<BanRecordsService>();
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
