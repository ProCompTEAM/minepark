using MDC.Common;
using MDC.Data.Enums;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class PhonesService : IPhonesService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public PhonesService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
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

        private async Task<long> CreatePhone(string subject, PhoneSubjectType subjectType)
        {
            Phone phone = new Phone
            {
                Subject = subject,
                SubjectType = subjectType,
                Number = await CreateNewNumber()
            };

            await databaseProvider.CreateAsync(phone);
            await databaseProvider.CommitAsync();

            return phone.Number;
        }

        private async Task<long> CreateNewNumber()
        {
            return Defaults.StartPhoneNumber + await databaseProvider.LongCountAsync<Phone>();
        }
    }
}
