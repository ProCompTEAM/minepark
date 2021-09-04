using MDC.Data.Dtos;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IFloatingTextsService
    {
        List<FloatingTextDto> GetAll(string unitId);

        Task<FloatingTextDto> Save(string unitId, LocalFloatingTextDto dto);

        Task<bool> Remove(string unitId, string world, double x, double y, double z); 
    }
}
