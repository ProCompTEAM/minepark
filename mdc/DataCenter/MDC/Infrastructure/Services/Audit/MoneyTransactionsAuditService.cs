using MDC.Data.Enums;
using MDC.Data.Models;
using MDC.Infrastructure.Services.Audit.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Audit
{
    public class MoneyTransactionsAuditService : IMoneyTransactionsAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IContextProvider contextProvider;

        public MoneyTransactionsAuditService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
            contextProvider = Store.GetProvider<ContextProvider>();
        }

        public async Task ProcessGiveOperation(string userName, double amount, PaymentMethod paymentMethod)
        {
            await CreateTransaction(userName, amount, paymentMethod, TransactionType.Give);
        }

        public async Task ProcessReduceOperation(string userName, double amount, PaymentMethod paymentMethod)
        {
            await CreateTransaction(userName, amount, paymentMethod, TransactionType.Reduce);
        }

        private async Task CreateTransaction(string userName, double amount, PaymentMethod targetAccount, TransactionType transactionType)
        {
            MoneyTransactionAuditRecord moneyTransactionAuditRecord = new MoneyTransactionAuditRecord
            {
                Subject = userName,
                UnitId = contextProvider.GetCurrentUnitId(),
                Amount = amount,
                TransactionType = transactionType,
                TargetAccount = targetAccount
            };

            await databaseProvider.CreateAsync(moneyTransactionAuditRecord);
        }
    }
}