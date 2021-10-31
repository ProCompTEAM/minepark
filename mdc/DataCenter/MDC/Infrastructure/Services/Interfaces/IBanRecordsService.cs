using MDC.Data.Dtos;
using MDC.Data.Models;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IBanRecordsService
    {
        Task<UserBanRecordDto> GetUserBanRecordDto(string userName);

        Task<bool> BanUser(string userName, string issuerName, DateTime releaseDate, string reason);

        Task<bool> PardonUser(string userName);

        Task<bool> IsBanned(string userName);

        Task UpdateBanStatus(User user);
    }
}
