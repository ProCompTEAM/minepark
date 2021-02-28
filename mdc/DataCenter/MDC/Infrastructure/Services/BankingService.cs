using System;

using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Infrastructure.Services.Audit;
using MDC.Infrastructure.Services.Audit.Interfaces;
using MDC.Data.Enums;
using MDC.Common;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class BankingService : IBankingService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IContextProvider contextProvider;

        private readonly IMoneyTransactionsAuditService moneyTransactionsAuditService;

        public BankingService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
            contextProvider = Store.GetProvider<ContextProvider>();

            moneyTransactionsAuditService = Store.GetService<MoneyTransactionsAuditService>();
        }

        public async Task<double> GetCash(string userName)
        {
            return (await GetBankAccount(userName)).Cash;
        }

        public async Task<double> GetDebit(string userName)
        {
            return (await GetBankAccount(userName)).Debit;
        }

        public async Task<double> GetCredit(string userName)
        {
            return (await GetBankAccount(userName)).Credit;
        }

        public async Task<double> GetAllMoney(string userName)
        {
            BankAccount bankAccount = await GetBankAccount(userName);

            return bankAccount.Cash + bankAccount.Debit + bankAccount.Credit;
        }

        public async Task<bool> ReduceCash(string userName, double amount)
        {
            BankAccount bankAccount = await GetBankAccount(userName);

            double money = RoundNumber(amount);

            if (!VerifyReduceOperation(bankAccount.Cash, money)) 
            {
                return false;
            }

            if (!await IncreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Cash -= money;
            await RegisterReduceOperation(userName, amount, PaymentMethod.Cash);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> ReduceDebit(string userName, double amount)
        {
            BankAccount bankAccount = await GetBankAccount(userName);

            double money = RoundNumber(amount);

            if (!VerifyReduceOperation(bankAccount.Debit, money)) 
            {
                return false;
            }

            if (!await IncreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Debit -= money;
            await RegisterReduceOperation(userName, amount, PaymentMethod.Debit);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> ReduceCredit(string userName, double amount)
        {  
            BankAccount bankAccount = await GetBankAccount(userName);

            double money = RoundNumber(amount);

            if (!VerifyReduceOperation(bankAccount.Credit, money)) 
            {
                return false;
            }

            if (!await IncreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Credit -= money;
            await RegisterReduceOperation(userName, amount, PaymentMethod.Credit);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> GiveCash(string userName, double amount)
        {
            BankAccount bankAccount = await GetBankAccount(userName);

            double money = RoundNumber(amount);

            if (!VerifyGiveOperation(money)) 
            {
                return false;
            }

            if (!await DecreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Cash += money;
            await RegisterGiveOperation(userName, amount, PaymentMethod.Cash);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> GiveDebit(string userName, double amount)
        {
            BankAccount bankAccount = await GetBankAccount(userName);

            double money = RoundNumber(amount);

            if (!VerifyGiveOperation(money)) 
            {
                return false;
            }

            if (!await DecreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Debit += money;
            await RegisterGiveOperation(userName, amount, PaymentMethod.Debit);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> GiveCredit(string userName, double amount)
        {
            BankAccount bankAccount = await GetBankAccount(userName);

            double money = RoundNumber(amount);

            if (!VerifyGiveOperation(money)) 
            {
                return false;
            }

            if (!await DecreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Credit += money;
            await RegisterGiveOperation(userName, amount, PaymentMethod.Credit);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> CreateEmptyBankAccount(string userName)
        {
            BankAccount bankAccount = GetDefaultBankTemplate(userName);

            await databaseProvider.CreateAsync(bankAccount);
            await databaseProvider.CommitAsync();

            return true;
        }

        public async Task<PaymentMethod> GetPaymentMethod(string userName)
        {
            BankAccount bankAccount = await GetBankAccount(userName);

            return bankAccount.PaymentMethod;
        }

        public async Task<bool> SwitchPaymentMethod(string userName, PaymentMethod method)
        {
            BankAccount bankAccount = await GetBankAccount(userName);

            if (bankAccount.PaymentMethod == method) 
            {
                return false;
            }

            bankAccount.PaymentMethod = method;
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<double> GetUnitBalance(string unitId)
        {
            return (await GetUnitBalanceModel(unitId)).Balance;
        }

        public async Task<bool> InitializeUnitBalance(string unitId)
        {
            if (!await databaseProvider.AnyAsync<UnitBalance>(b => b.UnitId == unitId)) 
            {
                await CreateUnitBalance(unitId);

                return true;
            }

            return false;
        }

        private async Task RegisterGiveOperation(string userName, double amount, PaymentMethod paymentMethod)
        {
            await moneyTransactionsAuditService.ProcessGiveOperation(userName, amount, paymentMethod);
        }

        private async Task RegisterReduceOperation(string userName, double amount, PaymentMethod paymentMethod)
        {
            await moneyTransactionsAuditService.ProcessReduceOperation(userName, amount, paymentMethod);
        }

        private bool VerifyReduceOperation(double moneyAmount, double decreaseAmount)
        {
            if (decreaseAmount < 0) 
            {
                return false;
            }

            return moneyAmount - decreaseAmount >= 0;
        }

        private bool VerifyGiveOperation(double increaseAmount)
        {
            return increaseAmount >= 0;
        }

        private async Task UpdateBankAccount(BankAccount bankAccount)
        {
            databaseProvider.Update(bankAccount);
            await databaseProvider.CommitAsync();
        }

        private async Task<BankAccount> GetBankAccount(string userName)
        {
            BankAccount bankAccount = await databaseProvider.SingleOrDefaultAsync<BankAccount>(b => b.Name == userName.ToLower());

            if (bankAccount == null) 
            {
                throw new InvalidOperationException("User not exist");
            }

            return bankAccount;
        }

        private async Task<bool> IncreaseUnitBalance(double increaseAmount)
        {
            string unitId = contextProvider.GetCurrentUnitId();

            UnitBalance unitBalance = await GetUnitBalanceModel(unitId);

            unitBalance.Balance += increaseAmount;

            databaseProvider.Update(unitBalance);

            return true;
        }

        private async Task<bool> DecreaseUnitBalance(double decreaseAmount)
        {
            string unitId = contextProvider.GetCurrentUnitId();

            UnitBalance unitBalance = await GetUnitBalanceModel(unitId);

            if (unitBalance.Balance < decreaseAmount)
            {
                return false;
            }

            unitBalance.Balance -= decreaseAmount;

            databaseProvider.Update(unitBalance);

            return true;
        }

        private async Task<UnitBalance> GetUnitBalanceModel(string unitId)
        {
            UnitBalance unitBalance = await databaseProvider.SingleOrDefaultAsync<UnitBalance>(b => b.UnitId == unitId);

            if (unitBalance == null) 
            {
                throw new InvalidOperationException("UnitID balance doesn't exist");
            }

            return unitBalance;
        }

        private async Task CreateUnitBalance(string unitId)
        {
            UnitBalance unitBalance = new UnitBalance
            {
                UnitId = unitId,
                Balance = Defaults.UnitStartBalance
            };

            await databaseProvider.CreateAsync(unitBalance);
            await databaseProvider.CommitAsync();
        }

        private BankAccount GetDefaultBankTemplate(string userName)
        {
            return new BankAccount
            {
                Name = userName.ToLower(),
                UnitId = contextProvider.GetCurrentUnitId(),
                Cash = 0.00,
                Debit = 0.00,
                Credit = 0.00,
                PaymentMethod = PaymentMethod.Cash
            };
        }

        private double RoundNumber(double number)
        {
            return Math.Round(number, Defaults.MoneyRoundDigitsAmount);
        }
    }
}
