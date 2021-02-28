using MDC.Data.Enums;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IBankingService
    {
        Task<double> GetCash(string unitId, string userName);

        Task<double> GetDebit(string unitId, string userName);

        Task<double> GetCredit(string unitId, string userName);

        Task<double> GetAllMoney(string unitId, string userName);

        Task<bool> ReduceCash(string unitId, string userName, double amount);

        Task<bool> ReduceDebit(string unitId, string userName, double amount);

        Task<bool> ReduceCredit(string unitId, string userName, double amount);

        Task<bool> GiveCash(string unitId, string userName, double amount);

        Task<bool> GiveDebit(string unitId, string userName, double amount);

        Task<bool> GiveCredit(string unitId, string userName, double amount);

        Task<bool> CreateEmptyBankAccount(string unitId, string userName);

        Task<PaymentMethod> GetPaymentMethod(string unitId, string userName);

        Task<bool> SwitchPaymentMethod(string unitId, string userName, PaymentMethod method);

        Task<double> GetUnitBalance(string unitId);

        Task<bool> InitializeUnitBalance(string unitId);
    }
}
