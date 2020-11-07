using MDC.Infrastructure.Providers.Interfaces;
using System;

namespace MDC.Infrastructure.Providers
{
    public class TokenProvider : ITokenProvider, IProvider
    {
        public string GenerateAuthToken()
        {
            return Guid.NewGuid().ToString();
        }
    }
}
