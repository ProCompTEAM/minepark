using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Audit.Interfaces
{
    public interface IUserTrafficAuditService
    {
        Task SaveUserJoinAttempt(string unitId, string userName);

        Task SaveUserQuitAttempt(string unitId, string userName);
    }
}
