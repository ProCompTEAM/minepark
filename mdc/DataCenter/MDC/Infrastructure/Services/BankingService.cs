using MDC.Data.Models;

using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Infrastructure.Services.Audit;
using MDC.Infrastructure.Services.Audit.Interfaces;

using MDC.Data.Enums;

using MDC.Common;

using System;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class BankingService : IBankingService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IMoneyTransactionsAuditService moneyTransactionsAuditService;

        public BankingService(
            DatabaseProvider databaseProvider,
            MoneyTransactionsAuditService moneyTransactionsAuditService)
        {
            this.databaseProvider = databaseProvider;
            this.moneyTransactionsAuditService = moneyTransactionsAuditService;
        }

        public async Task<double> GetCash(string unitId, string userName)
        {
            return (await GetBankAccount(unitId, userName)).Cash;
        }

        public async Task<double> GetDebit(string unitId, string userName)
        {
            return (await GetBankAccount(unitId, userName)).Debit;
        }

        public async Task<double> GetCredit(string unitId, string userName)
        {
            return (await GetBankAccount(unitId, userName)).Credit;
        }

        public async Task<double> GetAllMoney(string unitId, string userName)
        {
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

            return bankAccount.Cash + bankAccount.Debit + bankAccount.Credit;
        }

        public async Task<bool> ReduceCash(string unitId, string userName, double amount)
        {
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

            double money = RoundNumber(amount);

            if (!VerifyReduceOperation(bankAccount.Cash, money)) 
            {
                return false;
            }

            if (!await IncreaseUnitBalance(unitId, money))
            {
                return false;
            }

            bankAccount.Cash -= money;
            await RegisterReduceOperation(unitId, userName, amount, PaymentMethod.Cash);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> ReduceDebit(string unitId, string userName, double amount)
        {
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

            double money = RoundNumber(amount);

            if (!VerifyReduceOperation(bankAccount.Debit, money)) 
            {
                return false;
            }

            if (!await IncreaseUnitBalance(unitId, money))
            {
                return false;
            }

            bankAccount.Debit -= money;
            await RegisterReduceOperation(unitId, userName, amount, PaymentMethod.Debit);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> ReduceCredit(string unitId, string userName, double amount)
        {  
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

            double money = RoundNumber(amount);

            if (!VerifyReduceOperation(bankAccount.Credit, money)) 
            {
                return false;
            }

            if (!await IncreaseUnitBalance(unitId, money))
            {
                return false;
            }

            bankAccount.Credit -= money;
            await RegisterReduceOperation(unitId, userName, amount, PaymentMethod.Credit);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> GiveCash(string unitId, string userName, double amount)
        {
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

            double money = RoundNumber(amount);

            if (!VerifyGiveOperation(money)) 
            {
                return false;
            }

            if (!await DecreaseUnitBalance(unitId, money))
            {
                return false;
            }

            bankAccount.Cash += money;
            await RegisterGiveOperation(unitId, userName, amount, PaymentMethod.Cash);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> GiveDebit(string unitId, string userName, double amount)
        {
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

            double money = RoundNumber(amount);

            if (!VerifyGiveOperation(money)) 
            {
                return false;
            }

            if (!await DecreaseUnitBalance(unitId, money))
            {
                return false;
            }

            bankAccount.Debit += money;
            await RegisterGiveOperation(unitId, userName, amount, PaymentMethod.Debit);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> GiveCredit(string unitId, string userName, double amount)
        {
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

            double money = RoundNumber(amount);

            if (!VerifyGiveOperation(money)) 
            {
                return false;
            }

            if (!await DecreaseUnitBalance(unitId, money))
            {
                return false;
            }

            bankAccount.Credit += money;
            await RegisterGiveOperation(unitId, userName, amount, PaymentMethod.Credit);
            await UpdateBankAccount(bankAccount);

            return true;
        }

        public async Task<bool> CreateEmptyBankAccount(string unitId, string userName)
        {
            BankAccount bankAccount = GetDefaultBankTemplate(unitId, userName);

            await databaseProvider.CreateAsync(bankAccount);
            await databaseProvider.CommitAsync();

            return true;
        }
        public async Task<bool> TransferDebit(string unitId, string userName, string target, double amount)
        {
            if (!await Exists(unitId, target))
            {
                return false;
            }

            if (!await ReduceDebit(unitId, userName, amount))
            {
                return false;
            }

            await GiveDebit(unitId, target, amount);

            return true;
        }
        
        public async Task<bool> Exists(string unitId, string userName)
        {
            return await databaseProvider.AnyAsync<BankAccount>(
                bankAccount => bankAccount.UnitId == unitId && bankAccount.Name == userName
            );
        }
        public async Task<PaymentMethod> GetPaymentMethod(string unitId, string userName)
        {
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

            return bankAccount.PaymentMethod;
        }

        public async Task<bool> SwitchPaymentMethod(string unitId, string userName, PaymentMethod method)
        {
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

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
            if (!await databaseProvider.AnyAsync<UnitBalance>(unitBalance => unitBalance.UnitId == unitId)) 
            {
                await CreateUnitBalance(unitId);

                return true;
            }

            return false;
        }

        private async Task RegisterGiveOperation(string unitId, string userName, double amount, PaymentMethod paymentMethod)
        {
            await moneyTransactionsAuditService.ProcessGiveOperation(userName, unitId, amount, paymentMethod);
        }

        private async Task RegisterReduceOperation(string unitId, string userName, double amount, PaymentMethod paymentMethod)
        {
            await moneyTransactionsAuditService.ProcessGiveOperation(userName, unitId, amount, paymentMethod);
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

        private async Task<BankAccount> GetBankAccount(string unitId, string userName)
        {
            BankAccount bankAccount = await databaseProvider.SingleOrDefaultAsync<BankAccount>(
                bankAccount =>
                    bankAccount.Name == userName.ToLower() &&
                    bankAccount.UnitId == unitId
                );

            if (bankAccount == null) 
            {
                throw new InvalidOperationException("Bank not exist");
            }

            return bankAccount;
        }

        private async Task<bool> IncreaseUnitBalance(string unitId, double increaseAmount)
        {
            UnitBalance unitBalance = await GetUnitBalanceModel(unitId);

            unitBalance.Balance += increaseAmount;

            databaseProvider.Update(unitBalance);

            return true;
        }

        private async Task<bool> DecreaseUnitBalance(string unitId, double decreaseAmount)
        {
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
            UnitBalance unitBalance = await databaseProvider.SingleOrDefaultAsync<UnitBalance>(unitBalance => unitBalance.UnitId == unitId);

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

        private BankAccount GetDefaultBankTemplate(string unitId, string userName)
        {
            return new BankAccount
            {
                Name = userName.ToLower(),
                UnitId = unitId,
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
