using MDC.Common.Network;
using MDC.Data.Models;
using MDC.Infrastructure.Providers.Interfaces;

namespace MDC.Infrastructure.Providers
{
    public class ContextProvider : IContextProvider, IProvider
    {
        public string Address => Context.Current.Address;

        public string AccessToken => Context.Current.AccessToken;

        private readonly IDatabaseProvider databaseProvider;

        public ContextProvider()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
        }

        public bool Authorize()
        {
            return databaseProvider.Null<Credentials>(u => u.GeneratedToken == AccessToken);
        }
    }
}