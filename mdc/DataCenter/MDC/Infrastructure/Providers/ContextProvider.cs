using MDC.Data.Models;
using MDC.Infrastructure.Providers.Interfaces;
using System.Collections.Generic;

namespace MDC.Infrastructure.Providers
{
    public class UnitProvider : IUnitProvider, IProvider
    {
        private readonly IDatabaseProvider databaseProvider;

        private List<string> tokens = new List<string>();

        private static readonly Dictionary<string, string> tokenUnitIdPairs = new Dictionary<string, string>();

        public UnitProvider()
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

        public string GetCurrentUnitId(string accessToken)
        {
            if(tokenUnitIdPairs.ContainsKey(accessToken))
            {
                return tokenUnitIdPairs[accessToken];
            }

            return null;
        }

        public void SetCurrentUnitId(string accessToken, string unitId)
        {
            tokenUnitIdPairs[accessToken] = unitId;
        }
    }
}