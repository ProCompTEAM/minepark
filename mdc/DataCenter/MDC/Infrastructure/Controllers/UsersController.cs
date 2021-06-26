using MDC.Common.Network.HttpWeb;
using MDC.Data.Dtos;
using MDC.Data.Dtos.Audit;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    public class UsersController : IController
    {
        public string Route { get; set; } = "users";

        private readonly IUsersService usersService;

        public UsersController()
        {
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
            string unitId = requestContext.UnitId;
            await usersService.Create(unitId, user);
        }

        public async Task<UserDto> CreateInternal(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await usersService.CreateInternal(unitId, userName);
        }

        public async Task Update(UserDto user)
        {
            await usersService.Update(user);
        }

        public async Task UpdateJoinStatus(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            await usersService.UpdateJoinStatus(unitId, userName);
        }

        public async Task UpdateQuitStatus(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            await usersService.UpdateQuitStatus(unitId, userName);
        }

        public async Task SaveExecutedCommand(ExecutedCommandDto commandDto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            await usersService.SaveExecutedCommandAuditRecord(unitId, commandDto.Sender, commandDto.Command);
        }

        public async Task SaveChatMessage(ChatMessageDto messageDto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            await usersService.SaveChatMessageAuditRecord(unitId, messageDto.Sender, messageDto.Message);
        }
    }
}
