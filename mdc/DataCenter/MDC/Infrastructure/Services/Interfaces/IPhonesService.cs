namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IPhonesService
    {
        long CreateNumberForUser(string userName);

        long CreateNumberForOrganization(string organizationName);

        long GetNumberForUser(string userName);

        long GetNumberForOrganization(string organizationName);
    }
}
