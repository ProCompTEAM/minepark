using MDC.Common.Network.HttpWeb;
using MDC.Data.Dtos;
using MDC.Data.Dtos.Audit;
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
        
        public async Task<double> GetCash(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.GetCash(unitId, userName);
        }

        public async Task<double> GetDebit(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.GetDebit(unitId, userName);
        }

        public async Task<double> GetCredit(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.GetCredit(unitId, userName);
        }

        public async Task<double> GetAllMoney(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.GetAllMoney(unitId, userName);
        }

        public async Task<bool> ReduceCash(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.ReduceCash(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> ReduceDebit(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.ReduceDebit(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> ReduceCredit(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.ReduceCredit(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> GiveCash(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.GiveCash(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> GiveDebit(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.GiveDebit(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> GiveCredit(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.GiveCredit(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> Exists(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.Exists(unitId, userName);
        }

        public async Task<bool> TransferDebit(TransferDebitDto dto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.TransferDebit(unitId, dto.Name, dto.Target, dto.Amount);
        }

        public async Task<int> GetPaymentMethod(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return (int)await bankingService.GetPaymentMethod(unitId, userName);
        }

        public async Task<bool> SwitchPaymentMethod(PaymentMethodDto bankDto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.SwitchPaymentMethod(unitId, bankDto.Name, bankDto.Method);
        }

        public async Task<double> GetUnitBalance(RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await bankingService.GetUnitBalance(unitId);
        }
    }
}
