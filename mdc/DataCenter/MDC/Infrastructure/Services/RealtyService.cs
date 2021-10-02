using AutoMapper;
using MDC.Data.Dtos.Realty;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;

using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class RealtyService : IRealtyService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IMapper mapper;

        public RealtyService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
            mapper = Store.GetMapper();
        }

        public async Task<bool> CreateArea(string unitId, AreaInfoDto areaInfo)
        {
            return true;
        }
    }
}
