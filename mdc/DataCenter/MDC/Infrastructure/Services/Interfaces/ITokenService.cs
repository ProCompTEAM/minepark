using MDC.Data.Dtos;
using System;
using System.Collections.Generic;
using System.Text;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface ITokenService
    {
        Task<string> GenerateToken(string tag);

        Task RemoveToken(string token);

        List<CredentialsDto> GetTokens();
    }
}
