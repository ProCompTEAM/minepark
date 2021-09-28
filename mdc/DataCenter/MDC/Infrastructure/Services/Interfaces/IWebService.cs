using MDC.Data.Dtos;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IWebService
    {
        Task<UserWebProfileDto> GetUserProfile(string unitId, string userName);

        Task<string> GetPassword(string userName);
    }
}
