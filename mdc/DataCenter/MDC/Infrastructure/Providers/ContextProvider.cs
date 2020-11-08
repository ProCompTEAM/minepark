using MDC.Common.Network;
using MDC.Data.Models;
using MDC.Infrastructure.Providers.Interfaces;
using System.Collections.Generic;

namespace MDC.Infrastructure.Providers
{
    public class ContextProvider : IContextProvider, IProvider
    {
        public string Address => Context.Current.Address;

        public string AccessToken => Context.Current.AccessToken;

        private readonly IDatabaseProvider databaseProvider;

        private List<string> tokens = new List<string>();

        public ContextProvider()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
        }

        public void RestoreCredentials()
        {
            tokens = databaseProvider.GetAll<Credentials, string>(c => c.GeneratedToken);
        }

        public bool Authorize()
        {
            return tokens.Contains(AccessToken);
        }
    }
}