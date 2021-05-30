using MDC.Data.Enums;
using MDC.Infrastructure.Services.Audit.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using System.Threading.Tasks;
using MDC.Data.Models.Audit;

namespace MDC.Infrastructure.Services.Audit
{
    public class MoneyTransactionsAuditService : IMoneyTransactionsAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public MoneyTransactionsAuditService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
        }

        public async Task ProcessGiveOperation(string userName, string unitId, double amount, PaymentMethod paymentMethod)
        {
            await CreateTransaction(userName, unitId, amount, paymentMethod, TransactionType.Give);
        }

        public async Task ProcessReduceOperation(string userName, string unitId, double amount, PaymentMethod paymentMethod)
        {
            await CreateTransaction(userName, unitId, amount, paymentMethod, TransactionType.Reduce);
        }

        private async Task CreateTransaction(string userName, string unitId, double amount, PaymentMethod targetAccount, TransactionType transactionType)
        {
            MoneyTransactionAuditRecord moneyTransactionAuditRecord = new MoneyTransactionAuditRecord
            {
                Subject = userName,
                UnitId = unitId,
                Amount = amount,
                TransactionType = transactionType,
                TargetAccount = targetAccount
            };

            await databaseProvider.CreateAsync(moneyTransactionAuditRecord);
        }
    }
}