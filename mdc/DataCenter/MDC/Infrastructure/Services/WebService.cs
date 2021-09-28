using MDC.Data.Dtos;
using MDC.Data.Enums;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Services.Interfaces;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class WebService : IWebService, IService
    {
        private readonly DatabaseProvider databaseProvider;

        private readonly UsersService usersService;

        public WebService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();

            usersService = Store.GetService<UsersService>();
        }

        public async Task<UserWebProfileDto> GetUserProfile(string unitId, string userName)
        {
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

            if (bankAccount == null)
            {
                return null;
            }

            UserDto userDto = await usersService.GetUserDto(userName);

            return CreateUserWebProfileDto(userDto, bankAccount);
        }

        public async Task<string> GetPassword(string userName)
        {
            User user = await usersService.GetUser(userName);

            return user?.Password;
        }

        private UserWebProfileDto CreateUserWebProfileDto(UserDto userDto, BankAccount bankAccount)
        {
            UserWebProfileDto userWebProfileDto = new UserWebProfileDto();

            userWebProfileDto.FullName = userDto.FullName;
            userWebProfileDto.Privilege = GetUserPrivilege(userDto);
            userWebProfileDto.PhoneNumber = userDto.PhoneNumber;
            userWebProfileDto.MoneySummary = bankAccount.Cash + bankAccount.Debit + bankAccount.Credit;
            userWebProfileDto.MinutesPlayed = userDto.MinutesPlayed;
            userWebProfileDto.CreatedDate = userDto.CreatedDate;

            return userWebProfileDto;
        }

        private async Task<BankAccount> GetBankAccount(string unitId, string userName)
        {
            return await databaseProvider.SingleOrDefaultAsync<BankAccount>(
                bankAccount =>
                    bankAccount.Name == userName.ToLower() &&
                    bankAccount.UnitId == unitId
                );
        }

        private UserPrivilege GetUserPrivilege(UserDto userDto)
        {
            if (userDto.Vip == true)
            {
                return UserPrivilege.Vip;
            }

            if (userDto.Builder == true)
            {
                return UserPrivilege.Builder;
            }

            if (userDto.Realtor == true)
            {
                return UserPrivilege.Realtor;
            }

            if (userDto.Administrator == true)
            {
                return UserPrivilege.Administrator;
            }

            return UserPrivilege.None;
        }
    }
}
