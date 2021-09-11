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
    public class BansService : IBansService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IMapper mapper;

        public BansService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();

            mapper = Store.GetMapper();
        }

        public async Task<PlayerBanDto> GetPlayerBanInfo(string userName)
        {
            PlayerBan playerBan = await GetPlayerBan(userName);

            return mapper.Map<PlayerBanDto>(playerBan);
        }

        public async Task<bool> BanPlayer(string userName, string issuer, DateTime banEnd, string reason)
        {
            if (await IsBanned(userName))
            {
                return false;
            }

            if (banEnd <= DateTime.Now)
            {
                throw new InvalidOperationException("DateTime is in past");
            }

            PlayerBan playerBan = CreatePlayerBanModel(userName, issuer, banEnd, reason);
            await databaseProvider.CreateAsync(playerBan);

            User user = await GetUser(userName);
            user.Ban = playerBan;
            databaseProvider.Update(user);

            await databaseProvider.CommitAsync();

            return true;
        }

        public async Task<bool> UnbanPlayer(string userName)
        {
            if (!await IsBanned(userName))
            {
                return false;
            }

            User user = await GetUser(userName);

            PlayerBan playerBan = user.Ban;

            user.Ban = null;

            databaseProvider.Delete(playerBan);

            await databaseProvider.CommitAsync();

            return true;
        }

        public async Task<bool> IsBanned(string userName)
        {
            return await databaseProvider.AnyAsync<PlayerBan>(ban => ban.UserName == userName);
        }

        public async Task UpdateBanStatus(User user)
        {
            if (user.Ban == null)
            {
                return;
            }

            if (user.Ban.End <= DateTime.Now)
            {
                await OnBanEnd(user);
            }
        }

        private async Task OnBanEnd(User user)
        {
            PlayerBan playerBan = user.Ban;

            user.Ban = null;

            databaseProvider.Delete(playerBan);

            await databaseProvider.CommitAsync();
        }

        private PlayerBan CreatePlayerBanModel(string userName, string issuer, DateTime banEnd, string reason)
        {
            return new PlayerBan
            {
                UserName = userName,
                Issuer = issuer,
                End = banEnd,
                Reason = reason
            };
        }

        private async Task<PlayerBan> GetPlayerBan(string userName)
        {
            PlayerBan playerBan = await databaseProvider.SingleOrDefaultAsync<PlayerBan>(playerBan => playerBan.UserName == userName);

            if (playerBan == null)
            {
                throw new InvalidOperationException("Ban not found");
            }

            return playerBan;
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
