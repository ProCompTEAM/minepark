using AutoMapper;
using MDC.Data.Dtos;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Utilities;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class MapService : IMapService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IContextProvider contextProvider;

        private readonly IMapper mapper;

        public MapService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
            contextProvider = Store.GetProvider<ContextProvider>();
            mapper = Store.GetMapper();
        }

        public async Task<MapPoint> GetPoint(string name)
        {
            string unitId = contextProvider.GetCurrentUnitId();
            return await databaseProvider.SingleOrDefaultAsync<MapPoint>(p => p.UnitId == unitId && p.Name.ToLower() == name.ToLower());
        }

        public async Task<MapPointDto> GetPointDto(string name)
        {
            MapPoint point = await GetPoint(name);
            return mapper.Map<MapPointDto>(point);
        }

        public async Task<int> GetPointGroup(string name)
        {
            return (await GetPoint(name)).GroupId;
        }

        public List<MapPointDto> GetPointsByGroupDtos(int groupId)
        {
            string unitId = contextProvider.GetCurrentUnitId();
            List<MapPoint> points = databaseProvider.GetAll<MapPoint>(p => p.UnitId == unitId && p.GroupId == groupId);
            return mapper.Map<List<MapPointDto>>(points);
        }

        public List<MapPointDto> GetNearPointsDtos(LocalMapPointDto dto)
        {
            string unitId = contextProvider.GetCurrentUnitId();
            List<MapPoint> points = databaseProvider.GetAll<MapPoint>(p => p.UnitId == unitId && p.Level == dto.Level);
            points = points.Where(p => MathAggregator.Distance(dto.X, dto.Y, dto.Z, p.X, p.Y, p.Z) <= dto.Distance).ToList();

            return mapper.Map<List<MapPointDto>>(points);
        }

        public async Task SetPoint(MapPointDto pointDto)
        {
            MapPoint point = await GetPoint(pointDto.Name);

            if (point == null)
            {
                await CreatePoint(pointDto);
            }
            else
            {
                await UpdatePoint(point, pointDto);
            }
        }

        public async Task<bool> DeletePoint(string name)
        {
            MapPoint point = await GetPoint(name);

            if(point == null)
            {
                return false;
            }

            databaseProvider.Delete(point);
            await databaseProvider.CommitAsync();

            return true;
        }

        private async Task CreatePoint(MapPointDto pointDto)
        {
            MapPoint point = mapper.Map<MapPoint>(pointDto);
            point.UnitId = contextProvider.GetCurrentUnitId();
            await databaseProvider.CreateAsync(point);
            await databaseProvider.CommitAsync();
        }

        private async Task UpdatePoint(MapPoint point, MapPointDto newPointDto)
        {
            point = ObjectComparer.Merge(point, newPointDto,
                    u => u.Id,
                    u => u.Name,
                    u => u.UnitId,
                    u => u.CreatedDate
                );

            point.UnitId = contextProvider.GetCurrentUnitId();

            databaseProvider.Update(point);
            await databaseProvider.CommitAsync();
        }
    }
}
