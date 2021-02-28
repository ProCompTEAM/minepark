using MDC.Data.Enums;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IBankingService
    {
        Task<double> GetCash(string userName);

        Task<double> GetDebit(string userName);

        Task<double> GetCredit(string userName);

        Task<double> GetAllMoney(string userName);

        Task<bool> ReduceCash(string userName, double amount);

        Task<bool> ReduceDebit(string userName, double amount);

        Task<bool> ReduceCredit(string userName, double amount);

        Task<bool> GiveCash(string userName, double amount);

        Task<bool> GiveDebit(string userName, double amount);

        Task<bool> GiveCredit(string userName, double amount);

        Task<bool> CreateEmptyBankAccount(string userName);

        Task<PaymentMethod> GetPaymentMethod(string userName);

        Task<bool> SwitchPaymentMethod(string userName, PaymentMethod method);

        Task<double> GetUnitBalance(string unitId);

        Task<bool> InitializeUnitBalance(string unitId);
    }
}
