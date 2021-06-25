using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Audit.Interfaces
{
    public interface IExecutedCommandsAuditService
    {
        Task SaveExecutedCommandAuditRecord(string unitId, string userName, string command);
    }
}
