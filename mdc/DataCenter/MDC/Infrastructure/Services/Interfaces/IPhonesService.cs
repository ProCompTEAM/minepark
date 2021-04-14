using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IPhonesService
    {
        Task<long> CreateNumberForUser(string userName);

        Task<long> CreateNumberForOrganization(string organizationName);

        Task<long?> GetNumberForUser(string userName);

        Task<long?> GetNumberForOrganization(string organizationName);

        Task<string> GetUserNameByNumber(long number);
    }
}
