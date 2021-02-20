using System;

using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Data.Enums;
using MDC.Common;

namespace MDC.Infrastructure.Services
{
    public class BankingService : IBankingService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IContextProvider contextProvider;

        public BankingService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
            contextProvider = Store.GetProvider<ContextProvider>();
        }

        public double GetCash(string userName)
        {
            return GetBankAccount(userName).Cash;
        }

        public double GetDebit(string userName)
        {
            return GetBankAccount(userName).Debit;
        }

        public double GetCredit(string userName)
        {
            return GetBankAccount(userName).Credit;
        }

        public double GetAllMoney(string userName)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            return bankAccount.Cash + bankAccount.Debit + bankAccount.Credit;
        }

        public bool ReduceCash(string userName, double amount)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            if (!VerifyReduceOperation(bankAccount.Cash, amount)) 
            {
                return false;
            }

            double money = RoundNumber(amount);

            if (!IncreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Cash -= money;
            UpdateBankAccount(bankAccount);

            return true;
        }

        public bool ReduceDebit(string userName, double amount)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            if (!VerifyReduceOperation(bankAccount.Debit, amount)) 
            {
                return false;
            }

            double money = RoundNumber(amount);

            if (!IncreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Debit -= money;
            UpdateBankAccount(bankAccount);

            return true;
        }

        public bool ReduceCredit(string userName, double amount)
        {  
            BankAccount bankAccount = GetBankAccount(userName);

            if (!VerifyReduceOperation(bankAccount.Credit, amount)) 
            {
                return false;
            }

            double money = RoundNumber(amount);

            if (!IncreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Credit -= money;
            UpdateBankAccount(bankAccount);

            return true;
        }

        public bool GiveCash(string userName, double amount)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            if (!VerifyGiveOperation(amount)) 
            {
                return false;
            }

            double money = RoundNumber(amount);

            if (!DecreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Cash += money;
            UpdateBankAccount(bankAccount);

            return true;
        }

        public bool GiveDebit(string userName, double amount)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            if (!VerifyGiveOperation(amount)) 
            {
                return false;
            }

            double money = RoundNumber(amount);

            if (!DecreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Debit += money;
            UpdateBankAccount(bankAccount);

            return true;
        }

        public bool GiveCredit(string userName, double amount)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            if (!VerifyGiveOperation(amount)) 
            {
                return false;
            }

            double money = RoundNumber(amount);

            if (!DecreaseUnitBalance(money))
            {
                return false;
            }

            bankAccount.Credit += money;
            UpdateBankAccount(bankAccount);

            return true;
        }

        public bool CreateEmptyBankAccount(string userName)
        {
            BankAccount bankAccount = GetDefaultBankTemplate(userName);

            databaseProvider.Create(bankAccount);
            databaseProvider.Commit();

            return true;
        }

        public PaymentMethod GetPaymentMethod(string userName)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            return bankAccount.PaymentMethod;
        }

        public bool SwitchPaymentMethod(string userName, PaymentMethod method)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            if (bankAccount.PaymentMethod == method) 
            {
                return false;
            }

            bankAccount.PaymentMethod = method;
            UpdateBankAccount(bankAccount);

            return true;
        }

        public double GetUnitBalance(string unitId)
        {
            return GetUnitBalanceModel(unitId).Balance;
        }

        public bool InitializeUnitBalance(string unitId)
        {
            if (!databaseProvider.Any<UnitBalance>(b => b.UnitId == unitId)) 
            {
                CreateUnitBalance(unitId);

                return true;
            }

            return false;
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

        private void UpdateBankAccount(BankAccount bankAccount)
        {
            databaseProvider.Update(bankAccount);
            databaseProvider.Commit();
        }

        private BankAccount GetBankAccount(string userName)
        {
            BankAccount bankAccount = databaseProvider.SingleOrDefault<BankAccount>(b => b.Name == userName.ToLower());

            if (bankAccount == null) 
            {
                throw new InvalidOperationException("User not exist");
            }

            return bankAccount;
        }

        private bool IncreaseUnitBalance(double increaseAmount)
        {
            string unitId = contextProvider.GetCurrentUnitId();

            UnitBalance unitBalance = GetUnitBalanceModel(unitId);

            unitBalance.Balance += increaseAmount;

            databaseProvider.Update(unitBalance);

            return true;
        }

        private bool DecreaseUnitBalance(double decreaseAmount)
        {
            string unitId = contextProvider.GetCurrentUnitId();

            UnitBalance unitBalance = GetUnitBalanceModel(unitId);

            if (unitBalance.Balance < decreaseAmount)
            {
                return false;
            }

            unitBalance.Balance -= decreaseAmount;

            databaseProvider.Update(unitBalance);

            return true;
        }

        private UnitBalance GetUnitBalanceModel(string unitId)
        {
            UnitBalance unitBalance = databaseProvider.SingleOrDefault<UnitBalance>(b => b.UnitId == unitId);

            if (unitBalance == null) 
            {
                throw new InvalidOperationException("UnitID balance doesn't exist");
            }

            return unitBalance;
        }

        private void CreateUnitBalance(string unitId)
        {
            UnitBalance unitBalance = new UnitBalance
            {
                UnitId = unitId,
                Balance = Defaults.UnitStartBalance
            };

            databaseProvider.Create(unitBalance);
            databaseProvider.Commit();
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
