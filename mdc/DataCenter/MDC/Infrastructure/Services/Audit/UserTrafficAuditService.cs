using MDC.Data.Enums;

using MDC.Data.Models.Audit;

using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Audit.Interfaces;
using MDC.Infrastructure.Services.Interfaces;

using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Audit
{
    public class UserTrafficAuditService : IUserTrafficAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public UserTrafficAuditService(DatabaseProvider databaseProvider)
        {
            this.databaseProvider = databaseProvider;
        }

        public async Task SaveUserJoinAttempt(string unitId, string userName)
        {
            await CreateTrafficRecord(unitId, userName, UserTrafficType.Join);
        }

        public async Task SaveUserQuitAttempt(string unitId, string userName)
        {
            await CreateTrafficRecord(unitId, userName, UserTrafficType.Quit);
        }

        private async Task CreateTrafficRecord(string unitId, string userName, UserTrafficType userTrafficType)
        {
            UserTrafficAuditRecord userTrafficAuditRecord = new UserTrafficAuditRecord
            {
                Subject = userName,
                UnitId = unitId,
                UserTrafficType = userTrafficType
            };

            await databaseProvider.CreateAsync(userTrafficAuditRecord);
            await databaseProvider.CommitAsync();
        }
    }
}