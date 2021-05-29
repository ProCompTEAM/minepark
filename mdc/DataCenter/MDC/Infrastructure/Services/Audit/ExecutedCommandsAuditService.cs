using MDC.Infrastructure.Services.Audit.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using System.Threading.Tasks;
using MDC.Data.Models.Audit;

namespace MDC.Infrastructure.Services.Audit
{
    public class ExecutedCommandsAuditService : IExecutedCommandsAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public ExecutedCommandsAuditService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
        }

        public async Task SaveExecutedCommandAuditRecord(string userName, string unitId, string command)
        {
            ExecutedCommandAuditRecord executedCommandAuditRecord = new ExecutedCommandAuditRecord
            {
                Subject = userName,
                UnitId = unitId,
                Command = command
            };

            await databaseProvider.CreateAsync(executedCommandAuditRecord);
            await databaseProvider.CommitAsync();
        }
    }
}