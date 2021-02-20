using MDC.Data.Enums;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IBankingService
    {
        double GetCash(string userName);

        double GetDebit(string userName);

        double GetCredit(string userName);

        double GetAllMoney(string userName);

        bool ReduceCash(string userName, double amount);

        bool ReduceDebit(string userName, double amount);

        bool ReduceCredit(string userName, double amount);

        bool GiveCash(string userName, double amount);

        bool GiveDebit(string userName, double amount);

        bool GiveCredit(string userName, double amount);

        bool CreateEmptyBankAccount(string userName);

        PaymentMethod GetPaymentMethod(string userName);

        bool SwitchPaymentMethod(string userName, PaymentMethod method);

        double GetUnitBalance(string unitId);

        bool InitializeUnitBalance(string unitId);
    }
}
