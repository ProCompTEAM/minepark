using AutoMapper;
using MDC.Data.Dtos.Realty;
using MDC.Data.Models.Realty;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Utilities;
using System.Collections.Generic;
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

        public async Task<bool> CreateArea(string unitId, RealtyAreaInfoDto areaInfo)
        {
            RealtyArea area = mapper.Map<RealtyArea>(areaInfo);
            area.UnitId = unitId;

            if(IsAreaIntersect(area))
            {
                return false;
            }

            await databaseProvider.CreateAsync(area);
            await databaseProvider.CommitAsync();

            return true;
        }

        public async Task<bool> DeleteArea(string unitId, string areaName)
        {
            RealtyArea area = await GetAreaByName(unitId, areaName);

            if (area == null)
            {
                return false;
            }

            databaseProvider.Delete(area);
            await databaseProvider.CommitAsync();

            return true;
        }

        public async Task<bool> CreateObject(string unitId, RealtyObjectSetupDto objectDto)
        {
            RealtyObject realtyObject = mapper.Map<RealtyObject>(objectDto);
            realtyObject.UnitId = unitId;

            return true;
        }

        private List<RealtyArea> GetAreas(string unitId)
        {
            return databaseProvider.GetAll<RealtyArea>(area => area.UnitId == unitId);
        }

        private async Task<RealtyArea> GetAreaByName(string unitId, string name)
        {
            return await databaseProvider.SingleOrDefaultAsync<RealtyArea>(area => area.UnitId == unitId && area.Name == name);
        }

        private bool IsAreaIntersect(RealtyArea targetArea)
        {
            var mapAreas = GetAreas(targetArea.UnitId);

            foreach (RealtyArea mapArea in mapAreas)
            {
                if (MathAggregator.Intersect(mapArea.StartX, mapArea.StartZ, targetArea.StartX, targetArea.StartZ, mapArea.EndX, mapArea.EndZ)
                    || MathAggregator.Intersect(mapArea.StartX, mapArea.StartZ, targetArea.EndX, targetArea.EndZ, mapArea.EndX, mapArea.EndZ)
                    || MathAggregator.Intersect(targetArea.StartX, targetArea.StartZ, mapArea.StartX, mapArea.StartZ, targetArea.EndX, targetArea.EndZ)
                    || MathAggregator.Intersect(targetArea.StartX, targetArea.StartZ, mapArea.EndX, mapArea.EndZ, targetArea.EndX, targetArea.EndZ))
                {
                    return true;
                }
            }

            return false;
        }
    }
}
