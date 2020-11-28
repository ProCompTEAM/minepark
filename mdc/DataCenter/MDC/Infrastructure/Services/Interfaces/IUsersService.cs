using MDC.Data.Dtos;
using MDC.Data.Models;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IUsersService
    {
        bool Exist(string userName);

        User GetUser(string userName);

        User GetUser(int userId);

        UserDto GetUserDto(string userName);

        string GetPassword(string userName);

        bool ExistPassword(string userName);

        void SetPassword(string userName, string password);

        void ResetPassword(string userName);

        void Create(UserDto userDto);

        UserDto CreateInternal(string userName);

        void Update(UserDto userDto);

        void Delete(string userName);

        void UpdateJoinStatus(string userName);

        void UpdateQuitStatus(string userName);
    }
}
