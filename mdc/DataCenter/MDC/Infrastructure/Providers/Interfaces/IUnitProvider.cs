namespace MDC.Infrastructure.Providers.Interfaces
{
    public interface IUnitProvider
    {
        void RestoreCredentials();

        bool Authorize(string accessToken);

        string GetCurrentUnitId(string accessToken);

        void SetCurrentUnitId(string accessToken, string unitId);
    }
}
