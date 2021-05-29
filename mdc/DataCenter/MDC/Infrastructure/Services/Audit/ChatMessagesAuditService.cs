using MDC.Data.Models.Audit;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Audit.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Audit
{
    public class ChatMessagesAuditService : IChatMessagesAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public ChatMessagesAuditService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
        }

        public async Task SaveChatMessageAuditRecord(string userName, string unitId, string message)
        {
            ChatMessageAuditRecord chatMessageAuditRecord = new ChatMessageAuditRecord
            {
                Subject = userName,
                UnitId = unitId,
                Message = message
            };

            await databaseProvider.CreateAsync(chatMessageAuditRecord);
            await databaseProvider.CommitAsync();
        }
    }
}