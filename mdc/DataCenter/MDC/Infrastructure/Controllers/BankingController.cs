using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Threading.Tasks;

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
        
        public async Task<double> GetCash(string userName)
        {
            return await bankingService.GetCash(userName);
        }

        public async Task<double> GetDebit(string userName)
        {
            return await bankingService.GetDebit(userName);
        }

        public async Task<double> GetCredit(string userName)
        {
            return await bankingService.GetCredit(userName);
        }

        public async Task<double> GetAllMoney(string userName)
        {
            return await bankingService.GetAllMoney(userName);
        }

        public async Task<bool> ReduceCash(BankTransactionDto bankDto)
        {
            return await bankingService.ReduceCash(bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> ReduceDebit(BankTransactionDto bankDto)
        {
            return await bankingService.ReduceDebit(bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> ReduceCredit(BankTransactionDto bankDto)
        {
            return await bankingService.ReduceCredit(bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> GiveCash(BankTransactionDto bankDto)
        {
            return await bankingService.GiveCash(bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> GiveDebit(BankTransactionDto bankDto)
        {
            return await bankingService.GiveDebit(bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> GiveCredit(BankTransactionDto bankDto)
        {
            return await bankingService.GiveCredit(bankDto.Name, bankDto.Amount);
        }

        public async Task<int> GetPaymentMethod(string userName)
        {
            return (int)await bankingService.GetPaymentMethod(userName);
        }

        public async Task<bool> SwitchPaymentMethod(PaymentMethodDto bankDto)
        {
            return await bankingService.SwitchPaymentMethod(bankDto.Name, bankDto.Method);
        }

        public async Task<double> GetUnitBalance(string unitId)
        {
            return await bankingService.GetUnitBalance(unitId);
        }
    }
}
