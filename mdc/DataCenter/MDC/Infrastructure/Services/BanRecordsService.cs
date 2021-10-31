using AutoMapper;
using MDC.Data.Dtos;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using System;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class BanRecordsService : IBanRecordsService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IMapper mapper;

        public BanRecordsService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();

            mapper = Store.GetMapper();
        }

        public async Task<UserBanRecordDto> GetUserBanRecordDto(string userName)
        {
            UserBanRecord userBanRecord = await GetUserBanRecord(userName);

            return mapper.Map<UserBanRecordDto>(userBanRecord);
        }

        public async Task<bool> BanUser(string userName, string issuerName, DateTime releaseDate, string reason)
        {
            if (await IsBanned(userName))
            {
                return false;
            }

            if (releaseDate <= DateTime.Now)
            {
                throw new InvalidOperationException("Release date is in past");
            }

            UserBanRecord userBanRecord = CreateUserBanRecordModel(userName, issuerName, releaseDate, reason);
            await databaseProvider.CreateAsync(userBanRecord);

            User user = await GetUser(userName);
            user.BanRecord = userBanRecord;
            databaseProvider.Update(user);

            await databaseProvider.CommitAsync();

            return true;
        }

        public async Task<bool> PardonUser(string userName)
        {
            if (!await IsBanned(userName))
            {
                return false;
            }

            User user = await GetUser(userName);

            UserBanRecord userBanRecord = user.BanRecord;
            user.BanRecord = null;
            databaseProvider.Delete(userBanRecord);

            await databaseProvider.CommitAsync();

            return true;
        }

        public async Task<bool> IsBanned(string userName)
        {
            return await databaseProvider.AnyAsync<UserBanRecord>(userBanRecord => userBanRecord.UserName == userName);
        }

        public async Task UpdateBanStatus(User user)
        {
            if (user.BanRecord == null)
            {
                return;
            }

            if (user.BanRecord.ReleaseDate <= DateTime.Now)
            {
                await ReleaseUserFromBan(user);
            }
        }

        private async Task ReleaseUserFromBan(User user)
        {
            UserBanRecord userBanRecord = user.BanRecord;

            user.BanRecord = null;
            databaseProvider.Delete(userBanRecord);

            await databaseProvider.CommitAsync();
        }

        private UserBanRecord CreateUserBanRecordModel(string userName, string issuerName, DateTime releaseDate, string reason)
        {
            return new UserBanRecord
            {
                UserName = userName,
                IssuerName = issuerName,
                ReleaseDate = releaseDate,
                Reason = reason
            };
        }

        private async Task<UserBanRecord> GetUserBanRecord(string userName)
        {
            UserBanRecord userBanRecord = await databaseProvider.SingleOrDefaultAsync<UserBanRecord>(userBanRecord => userBanRecord.UserName == userName);

            if (userBanRecord == null)
            {
                throw new InvalidOperationException("Ban record not found");
            }

            return userBanRecord;
        }

        private async Task<User> GetUser(string userName)
        {
            User user = await databaseProvider.SingleOrDefaultAsync<User>(user => user.Name == userName); 

            if (user == null)
            {
                throw new InvalidOperationException("User doesn't exist");
            }

            return user;
        }
    }
}
