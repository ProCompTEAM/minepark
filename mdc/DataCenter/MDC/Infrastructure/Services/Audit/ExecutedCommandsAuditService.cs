using MDC.Data.Enums;
using MDC.Data.Models;
using MDC.Infrastructure.Services.Audit.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Audit
{
    public class ExecutedCommandsAuditService : IExecutedCommandsAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public ExecutedCommandsAuditService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
        }

        public async Task ProcessExecuteOperation(string userName, string unitId, string command)
        {
            await CreateCommandInput(userName, unitId, command);
        }

        private async Task CreateCommandInput(string userName, string unitId, string command)
        {
            ExecutedCommandAuditRecord executedCommandAuditRecord = new ExecutedCommandAuditRecord
            {
                Subject = userName,
                UnitId = unitId,
                Command = command
            };

            await databaseProvider.CreateAsync(executedCommandAuditRecord);
        }
    }
}