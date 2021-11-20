using MDC.Common;
using MDC.Data.Enums;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;

using System;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class PhonesService : IPhonesService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public PhonesService(DatabaseProvider databaseProvider)
        {
            this.databaseProvider = databaseProvider;
        }

        public async Task<long> CreateNumberForUser(string userName)
        {
            return await CreatePhone(userName, PhoneSubjectType.User);
        }

        public async Task<long> CreateNumberForOrganization(string organizationName)
        {
            return await CreatePhone(organizationName, PhoneSubjectType.Organization);
        }

        public async Task<long?> GetNumberForUser(string userName)
        {
            Phone phone = await databaseProvider.SingleOrDefaultAsync<Phone>(
                phone => phone.SubjectType == PhoneSubjectType.User && 
                phone.Subject.ToLower() == userName.ToLower());
            return phone?.Number;
        }

        public async Task<long?> GetNumberForOrganization(string organizationName)
        {
            Phone phone = await databaseProvider.SingleOrDefaultAsync<Phone>(
                phone => phone.SubjectType == PhoneSubjectType.Organization &&
                phone.Subject == organizationName);
            return phone?.Number;
        }

        public async Task<string> GetUserNameByNumber(long number)
        {
            Phone phone = await databaseProvider.SingleOrDefaultAsync<Phone>(
                phone => phone.SubjectType == PhoneSubjectType.User &&
                phone.Number == number);
            return phone?.Subject;
        }

        public async Task<double> GetBalance(string userName)
        {
            Phone phone = await GetUserPhone(userName);

            return phone.Balance;
        }

        public async Task<bool> AddBalance(string userName, double amount)
        {
            Phone phone = await GetUserPhone(userName);

            if (amount < 1)
            {
                return false;
            }

            amount = RoundNumber(amount);

            phone.Balance += amount;

            databaseProvider.Update(phone);
            await databaseProvider.CommitAsync();

            return true;
        }

        public async Task<bool> ReduceBalance(string userName, double amount)
        {
            Phone phone = await GetUserPhone(userName);

            if (amount < 1)
            {
                return false;
            }

            amount = RoundNumber(amount);

            if (amount > phone.Balance)
            {
                return false;
            }

            phone.Balance -= amount;

            databaseProvider.Update(phone);
            await databaseProvider.CommitAsync();

            return true;
        }

        private async Task<long> CreatePhone(string subject, PhoneSubjectType subjectType)
        {
            Phone phone = new Phone
            {
                Subject = subject,
                SubjectType = subjectType,
                Number = await CreateNewNumber(),
                Balance = 0.00
            };

            await databaseProvider.CreateAsync(phone);
            await databaseProvider.CommitAsync();

            return phone.Number;
        }

        private async Task<long> CreateNewNumber()
        {
            return Defaults.StartPhoneNumber + await databaseProvider.LongCountAsync<Phone>();
        }

        private async Task<Phone> GetUserPhone(string userName)
        {
            Phone phone = await databaseProvider.SingleOrDefaultAsync<Phone>(
                phone => phone.SubjectType == PhoneSubjectType.User &&
                phone.Subject == userName);

            if (phone == null)
            {
                throw new InvalidOperationException("Phone name doesn't exist");
            }

            return phone;
        }

        private double RoundNumber(double number)
        {
            return Math.Round(number, Defaults.MoneyRoundDigitsAmount);
        }
    }
}
