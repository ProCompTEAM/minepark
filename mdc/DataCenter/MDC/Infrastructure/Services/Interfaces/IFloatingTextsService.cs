using MDC.Data.Dtos;
using MDC.Data.Models;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IFloatingTextsService
    {
        List<FloatingTextDto> GetAll(string unitId);

        Task Save(string unitId, string text, string level, double x, double y, double z);

        Task<bool> Remove(string unitId, string level, double x, double y, double z); 
    }
}
