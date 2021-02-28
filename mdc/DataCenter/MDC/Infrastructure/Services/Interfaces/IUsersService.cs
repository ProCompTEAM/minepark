using MDC.Data.Dtos;
using MDC.Data.Models;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IUsersService
    {
        Task<bool> Exist(string userName);

        Task<User> GetUser(string userName);

        Task<User> GetUser(int userId);

        Task<UserDto> GetUserDto(string userName);

        Task<string> GetPassword(string userName);

        Task<bool> ExistPassword(string userName);

        Task SetPassword(string userName, string password);

        Task ResetPassword(string userName);

        Task Create(UserDto userDto);

        Task<UserDto> CreateInternal(string userName);

        Task Update(UserDto userDto);

        Task UpdateJoinStatus(string userName);

        Task UpdateQuitStatus(string userName);
    }
}
