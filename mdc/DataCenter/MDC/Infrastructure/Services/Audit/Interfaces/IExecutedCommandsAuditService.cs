using MDC.Data.Enums;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Audit.Interfaces
{
    public interface IExecutedCommandsAuditService
    {
        Task ProcessExecuteOperation(string unitId, string userName, string command);
    }
}
