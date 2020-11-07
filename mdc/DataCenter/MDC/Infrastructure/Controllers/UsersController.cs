using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;

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

        public bool Exist(string userName)
        {
            return usersService.Exist(userName);
        }

        public UserDto GetUser(string userName)
        {
            return usersService.GetUserDto(userName);
        }

        public string GetPassword(string userName)
        {
            return usersService.GetPassword(userName);
        }

        public void SetPassword(PasswordDto passwordDto)
        {
            usersService.SetPassword(passwordDto.Name, passwordDto.Password);
        }

        public void ResetPassword(string userName)
        {
            usersService.ResetPassword(userName);
        }

        public void Create(UserDto user)
        {
            usersService.Create(user);
        }

        public UserDto CreateInternal(string userName)
        {
            return usersService.CreateInternal(userName);
        }

        public void Update(UserDto user)
        {
            usersService.Update(user);
        }

        public void Delete(string userName)
        {
            usersService.Delete(userName);
        }

        public void UpdateJoinStatus(string userName)
        {
            usersService.UpdateJoinStatus(userName);
        }

        public void UpdateQuitStatus(string userName)
        {
            usersService.UpdateQuitStatus(userName);
        }
    }
}
