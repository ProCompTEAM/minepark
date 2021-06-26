namespace MDC.Infrastructure.Providers.Interfaces
{
    public interface IAuthorizationProvider
    {
        void RestoreCredentials();

        bool Authorize(string accessToken);

        void AddAccessToken(string accessToken);

        void RemoveAccessToken(string accessToken);
    }
}
