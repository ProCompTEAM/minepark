using MDC.Common.Network.HttpWeb;
using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    public class UsersController : IController
    {
        public string Route { get; set; } = "users";

        private readonly IUnitProvider unitProvider;

        private readonly IUsersService usersService;

        public UsersController()
        {
            unitProvider = Store.GetProvider<UnitProvider>();
            usersService = Store.GetService<UsersService>();
        }

        public async Task<bool> Exist(string userName)
        {
            return await usersService.Exist(userName);
        }

        public async Task<UserDto> GetUser(string userName)
        {
            return await usersService.GetUserDto(userName);
        }

        public async Task<string> GetPassword(string userName)
        {
            return await usersService.GetPassword(userName);
        }

        public async Task<bool> ExistPassword(string userName)
        {
            return await usersService.ExistPassword(userName);
        }

        public async Task SetPassword(PasswordDto passwordDto)
        {
            await usersService.SetPassword(passwordDto.Name, passwordDto.Password);
        }

        public async Task ResetPassword(string userName)
        {
            await usersService.ResetPassword(userName);
        }

        public async Task Create(UserDto user, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            await usersService.Create(unitId, user);
        }

        public async Task<UserDto> CreateInternal(string userName, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await usersService.CreateInternal(unitId, userName);
        }

        public async Task Update(UserDto user)
        {
            await usersService.Update(user);
        }

        public async Task UpdateJoinStatus(string userName)
        {
            await usersService.UpdateJoinStatus(userName);
        }

        public async Task UpdateQuitStatus(string userName)
        {
            await usersService.UpdateQuitStatus(userName);
        }
    }
}
