using MDC.Data.Models;
using MDC.Infrastructure.Providers.Interfaces;
using System.Collections.Generic;

namespace MDC.Infrastructure.Providers
{
    public class AuthorizationProvider : IAuthorizationProvider, IProvider
    {
        private readonly IDatabaseProvider databaseProvider;

        private List<string> tokens = new List<string>();

        public AuthorizationProvider()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
        }

        public void RestoreCredentials()
        {
            tokens = databaseProvider.GetAll<Credentials, string>(c => c.GeneratedToken);
        }

        public bool Authorize(string accessToken)
        {
            return tokens.Contains(accessToken);
        }

        public void AddAccessToken(string accessToken)
        {
            if (!tokens.Contains(accessToken))
            {
                tokens.Add(accessToken);
            }
        }

        public void RemoveAccessToken(string accessToken)
        {
            if (tokens.Contains(accessToken))
            {
                tokens.Remove(accessToken);
            }
        }
    }
}