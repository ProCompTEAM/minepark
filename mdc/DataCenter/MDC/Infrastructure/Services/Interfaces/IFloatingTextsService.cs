using MDC.Data.Dtos;
using MDC.Data.Models;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IFloatingTextsService
    {
        List<FloatingTextDto> GetAll(string unitId);

        Task<FloatingTextDto> Save(string unitId, LocalFloatingTextDto dto);

        Task<bool> Remove(string unitId, string level, int x, int y, int z); 
    }
}
