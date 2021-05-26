using AutoMapper;
using MDC.Data.Dtos;
using MDC.Data.Enums;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Infrastructure.Services.Audit;
using MDC.Infrastructure.Services.Audit.Interfaces;
using MDC.Utilities;
using System;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class UsersService : IUsersService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IDateTimeProvider dateTimeProvider;

        private readonly IPhonesService phonesService;

        private readonly IBankingService bankingService;

        private readonly IMapper mapper;

        private readonly IExecutedCommandsAuditService executedCommandsAuditService;

        public UsersService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
            dateTimeProvider = Store.GetProvider<DateTimeProvider>();

            phonesService = Store.GetService<PhonesService>();
            bankingService = Store.GetService<BankingService>();

            mapper = Store.GetMapper();
        }

        public Task<bool> Exist(string userName)
        {
            return databaseProvider.AnyAsync<User>(u => u.Name == userName);
        }

        public async Task<User> GetUser(string userName)
        {
            return await databaseProvider.SingleOrDefaultAsync<User>(u => u.Name.ToLower() == userName.ToLower());
        }

        public async Task<User> GetUser(int userId)
        {
            return await databaseProvider.FindPrimary<User>(userId);
        }

        public async Task<UserDto> GetUserDto(string userName)
        {
            User user = await GetUser(userName);
            UserDto userDto = mapper.Map<UserDto>(user);
            userDto.PhoneNumber = (long)await phonesService.GetNumberForUser(userName);
            return userDto;
        }

        public async Task<string> GetPassword(string userName)
        {
            User user = await GetUser(userName);
            return user.Password;
        }

        public async Task<bool> ExistPassword(string userName)
        {
            User user = await GetUser(userName);
            return user.Password != null;
        }

        public async Task SetPassword(string userName, string password)
        {
            User user = await GetUser(userName);
            user.Password = password;
            databaseProvider.Update(user);
            await databaseProvider.CommitAsync();
        }

        public async Task ResetPassword(string userName)
        {
            await SetPassword(userName, null);
        }

        public async Task Create(string unitId, UserDto userDto)
        {
            await ValidateIsUserExist(userDto.Name);

            User user = mapper.Map<User>(userDto);
            await databaseProvider.CreateAsync(user);
            await databaseProvider.CommitAsync();

            await phonesService.CreateNumberForUser(user.Name);
            await bankingService.CreateEmptyBankAccount(unitId, user.Name);
        }

        public async Task<UserDto> CreateInternal(string unitId, string userName)
        {
            await ValidateIsUserExist(userName);

            User user = GetDefaultUserTemplate(userName);
            await databaseProvider.CreateAsync(user);
            await databaseProvider.CommitAsync();

            await bankingService.CreateEmptyBankAccount(unitId, userName);

            long phoneNumber = await phonesService.CreateNumberForUser(user.Name);

            UserDto userDto = mapper.Map<UserDto>(user);
            userDto.PhoneNumber = phoneNumber;

            return userDto;
        }

        public async Task Update(UserDto userDto)
        {
            User user = await GetUser(userDto.Name);

            user = ObjectComparer.Merge(user, userDto, 
                    u => u.Id,
                    u => u.Name,
                    u => u.MinutesPlayed,
                    u => u.JoinedDate,
                    u => u.LeftDate,
                    u => u.CreatedDate,
                    u => u.UpdatedDate
                );
            
            databaseProvider.Update(user);
            await databaseProvider.CommitAsync();
        }

        public async Task UpdateJoinStatus(string userName)
        {
            User user = await GetUser(userName);
            user.JoinedDate = dateTimeProvider.Now;
            databaseProvider.Update(user);
            await databaseProvider.CommitAsync();
        }

        public async Task UpdateQuitStatus(string userName)
        {
            User user = await GetUser(userName);
            user.LeftDate = dateTimeProvider.Now;
            user.MinutesPlayed += GetMinutesLeft(user.JoinedDate, user.LeftDate);
            databaseProvider.Update(user);
            await databaseProvider.CommitAsync();
        }

        public async Task ExecuteCommand(string unitId, string userName, string command)
        {
            await RegisterExecuteCommand(unitId, userName, command);
        }

        private async Task RegisterExecuteCommand(string unitId, string userName, string command)
        {
            await executedCommandsAuditService.ProcessExecuteOperation(userName, unitId, command);
        }

        private int GetMinutesLeft(DateTime joinedDate, DateTime leftDate)
        {
            return (int) (leftDate - joinedDate).TotalMinutes;
        }

        private string CreateFullName(string userName)
        {
            return userName.Replace('_', ' ');
        }

        private User GetDefaultUserTemplate(string userName)
        {
            return new User
            {
                Name = userName,
                FullName = CreateFullName(userName),
                Organisation = 0,
                Bonus = 3,
                MinutesPlayed = 0,
                Vip = false,
                Administrator = false,
                Builder = false,
                Realtor = false
            };
        }

        private async Task ValidateIsUserExist(string userName)
        {
            if (await Exist(userName))
            {
                throw new InvalidOperationException("User already exists.");
            }
        }
    }
}
