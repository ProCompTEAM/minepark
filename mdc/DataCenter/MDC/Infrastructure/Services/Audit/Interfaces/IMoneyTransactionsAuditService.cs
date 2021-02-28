using MDC.Data.Enums;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Audit.Interfaces
{
    public interface IMoneyTransactionsAuditService
    {
        Task ProcessGiveOperation(string userName, double amount, PaymentMethod paymentMethod);

        Task ProcessReduceOperation(string userName, double amount, PaymentMethod paymentMethod);
    }
}
