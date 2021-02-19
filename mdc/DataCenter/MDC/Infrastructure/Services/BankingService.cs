using System;

using MDC.Data.Dtos;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Data.Enums;

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

            bankAccount.Cash -= amount;

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

            bankAccount.Debit -= amount;

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

            bankAccount.Credit -= amount;
            UpdateBankAccount(bankAccount);

            return true;
        }

        public bool GiveCash(string userName, double amount)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            if (!VerifyGiveOperation(bankAccount.Credit, amount)) 
            {
                return false;
            }

            bankAccount.Cash += amount;
            UpdateBankAccount(bankAccount);

            return true;
        }

        public bool GiveDebit(string userName, double amount)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            if (!VerifyGiveOperation(bankAccount.Credit, amount)) 
            {
                return false;
            }

            bankAccount.Debit += amount;
            UpdateBankAccount(bankAccount);

            return true;
        }

        public bool GiveCredit(string userName, double amount)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            if (!VerifyGiveOperation(bankAccount.Credit, amount)) 
            {
                return false;
            }

            bankAccount.Credit += amount;
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

        public int GetPaymentMethod(string userName)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            return (int) bankAccount.PaymentMethod;
        }

        public bool SwitchPaymentMethod(string userName, int method)
        {
            BankAccount bankAccount = GetBankAccount(userName);

            if ((int) bankAccount.PaymentMethod == method) 
            {
                return false;
            }

            bankAccount.PaymentMethod = (PaymentMethod) method;
            UpdateBankAccount(bankAccount);

            return true;
        }

        private bool VerifyReduceOperation(double moneyAmount, double decreaseAmount)
        {
            if (decreaseAmount < 0) 
            {
                return false;
            }

            if (Math.Round(decreaseAmount, 2) != decreaseAmount) 
            {
                return false;
            }

            return moneyAmount - decreaseAmount >= 0;
        }

        private bool VerifyGiveOperation(double moneyAmount, double increaseAmount)
        {
            if (Math.Round(increaseAmount, 2) != increaseAmount) 
            {
                return false;
            }

            return increaseAmount >= 0;
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
    }
}
