namespace MDC.Infrastructure.Providers.Interfaces
{
    public interface IContextProvider
    {
        string Address { get; }

        string AccessToken { get; }

        bool Authorize();
    }
}
