using AutoMapper;
using MDC.Data.Dtos;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Services.Interfaces;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class TokenService : IService, ITokenService
    {
        private readonly TokenProvider tokenProvider;

        private readonly UnitProvider unitProvider;

        private readonly DatabaseProvider databaseProvider;

        private readonly IMapper mapper;

        public TokenService()
        {
            tokenProvider = Store.GetProvider<TokenProvider>();

            unitProvider = Store.GetProvider<UnitProvider>();

            databaseProvider = Store.GetProvider<DatabaseProvider>();

            mapper = Store.GetMapper();
        }

        public async Task<string> GenerateToken(string tag = null)
        {
            string generatedToken = tokenProvider.GenerateAuthToken();

            Credentials credentials = GetCredentialsModel(generatedToken, tag);

            await databaseProvider.CreateAsync(credentials);
            await databaseProvider.CommitAsync();

            unitProvider.AddAccessToken(generatedToken);

            return generatedToken;
        }

        public async Task RemoveToken(string token)
        {
            databaseProvider.Delete<Credentials>(credentials => credentials.GeneratedToken == token);
            await databaseProvider.CommitAsync();

            unitProvider.RemoveAccessToken(token);
        }

        public List<CredentialsDto> GetTokens()
        {
            List<Credentials> credentials = databaseProvider.GetAll<Credentials>();

            return mapper.Map<List<CredentialsDto>>(credentials);
        }

        private Credentials GetCredentialsModel(string generatedToken, string tag)
        {
            return new Credentials
            {
                GeneratedToken = generatedToken,
                Tag = tag
            };
        }
    }
}