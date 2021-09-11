using MDC.Data.Dtos;
using MDC.Data.Models;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IBansService
    {
        public Task<PlayerBanDto> GetPlayerBanInfo(string userName);

        public Task<bool> BanPlayer(string userName, string issuer, DateTime banEnd, string reason);

        public Task<bool> UnbanPlayer(string userName);

        public Task<bool> IsBanned(string userName);

        public Task UpdateBanStatus(User user);
    }
}
