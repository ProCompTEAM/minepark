using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;

namespace MDC.Infrastructure.Controllers
{
    public class BankingController : IController
    {
        public string Route { get; set; } = "banking";

        private readonly IBankingService bankingService;

        public BankingController()
        {
            bankingService = Store.GetService<BankingService>();
        }
        
        public double GetCash(string userName)
        {
            return bankingService.GetCash(userName);
        }

        public double GetDebit(string userName)
        {
            return bankingService.GetDebit(userName);
        }

        public double GetCredit(string userName)
        {
            return bankingService.GetCredit(userName);
        }

        public double GetAllMoney(string userName)
        {
            return bankingService.GetAllMoney(userName);
        }

        public bool ReduceCash(BankTransactionDto bankDto)
        {
            return bankingService.ReduceCash(bankDto.Name, bankDto.Amount);
        }

        public bool ReduceDebit(BankTransactionDto bankDto)
        {
            return bankingService.ReduceDebit(bankDto.Name, bankDto.Amount);
        }

        public bool ReduceCredit(BankTransactionDto bankDto)
        {
            return bankingService.ReduceCredit(bankDto.Name, bankDto.Amount);
        }

        public bool GiveCash(BankTransactionDto bankDto)
        {
            return bankingService.GiveCash(bankDto.Name, bankDto.Amount);
        }

        public bool GiveDebit(BankTransactionDto bankDto)
        {
            return bankingService.GiveDebit(bankDto.Name, bankDto.Amount);
        }

        public bool GiveCredit(BankTransactionDto bankDto)
        {
            return bankingService.GiveCredit(bankDto.Name, bankDto.Amount);
        }

        public int GetPaymentMethod(string userName)
        {
            return (int) bankingService.GetPaymentMethod(userName);
        }

        public bool SwitchPaymentMethod(PaymentMethodDto bankDto)
        {
            return bankingService.SwitchPaymentMethod(bankDto.Name, bankDto.Method);
        }

        public double GetUnitBalance(string unitId)
        {
            return bankingService.GetUnitBalance(unitId);
        }
    }
}
