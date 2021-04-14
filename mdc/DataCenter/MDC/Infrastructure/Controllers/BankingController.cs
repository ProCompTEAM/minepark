using MDC.Common.Network.HttpWeb;
using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    public class BankingController : IController
    {
        public string Route { get; set; } = "banking";

        private readonly IUnitProvider unitProvider;

        private readonly IBankingService bankingService;

        public BankingController()
        {
            unitProvider = Store.GetProvider<UnitProvider>();
            bankingService = Store.GetService<BankingService>();
        }
        
        public async Task<double> GetCash(string userName, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.GetCash(unitId, userName);
        }

        public async Task<double> GetDebit(string userName, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.GetDebit(unitId, userName);
        }

        public async Task<double> GetCredit(string userName, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.GetCredit(unitId, userName);
        }

        public async Task<double> GetAllMoney(string userName, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.GetAllMoney(unitId, userName);
        }

        public async Task<bool> ReduceCash(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.ReduceCash(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> ReduceDebit(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.ReduceDebit(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> ReduceCredit(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.ReduceCredit(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> GiveCash(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.GiveCash(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> GiveDebit(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.GiveDebit(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<bool> GiveCredit(BankTransactionDto bankDto, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.GiveCredit(unitId, bankDto.Name, bankDto.Amount);
        }

        public async Task<int> GetPaymentMethod(string userName, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return (int)await bankingService.GetPaymentMethod(unitId, userName);
        }

        public async Task<bool> SwitchPaymentMethod(PaymentMethodDto bankDto, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.SwitchPaymentMethod(unitId, bankDto.Name, bankDto.Method);
        }

        public async Task<double> GetUnitBalance(RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await bankingService.GetUnitBalance(unitId);
        }
    }
}
