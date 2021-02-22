using MDC.Data.Enums;

namespace MDC.Infrastructure.Services.Audit.Interfaces
{
    public interface IMoneyTransactionsAuditService
    {
        void ProcessGiveOperation(string userName, double amount, PaymentMethod paymentMethod);

        void ProcessReduceOperation(string userName, double amount, PaymentMethod paymentMethod);
    }
}
