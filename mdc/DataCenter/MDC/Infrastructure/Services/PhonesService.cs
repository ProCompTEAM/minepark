using MDC.Common;
using MDC.Data.Enums;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;

namespace MDC.Infrastructure.Services
{
    public class PhonesService : IPhonesService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public PhonesService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
        }

        public long CreateNumberForUser(string userName)
        {
            return CreatePhone(userName.ToLower(), PhoneSubjectType.User);
        }

        public long CreateNumberForOrganization(string organizationName)
        {
            return CreatePhone(organizationName, PhoneSubjectType.Organization);
        }

        public long? GetNumberForUser(string userName)
        {
            Phone phone = databaseProvider.SingleOrDefault<Phone>(
                phone => phone.SubjectType == PhoneSubjectType.User && 
                phone.Subject == userName.ToLower());
            return phone?.Number;
        }

        public long? GetNumberForOrganization(string organizationName)
        {
            Phone phone = databaseProvider.SingleOrDefault<Phone>(
                phone => phone.SubjectType == PhoneSubjectType.Organization &&
                phone.Subject == organizationName);
            return phone?.Number;
        }

        public string GetUserNameByNumber(long number)
        {
            Phone phone = databaseProvider.SingleOrDefault<Phone>(
                phone => phone.SubjectType == PhoneSubjectType.User &&
                phone.Number == number);
            return phone?.Subject;
        }

        private long CreatePhone(string subject, PhoneSubjectType subjectType)
        {
            Phone phone = new Phone
            {
                Subject = subject,
                SubjectType = subjectType,
                Number = CreateNewNumber()
            };

            databaseProvider.Create(phone);
            databaseProvider.Commit();

            return phone.Number;
        }

        private long CreateNewNumber()
        {
            return Defaults.StartPhoneNumber + databaseProvider.Count<Phone>();
        }
    }
}
