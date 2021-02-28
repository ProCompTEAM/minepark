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

        private readonly IMapper mapper;

        public MapService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
            mapper = Store.GetMapper();
        }

        public async Task<MapPoint> GetPoint(string unitId, string name)
        {
            return await databaseProvider.SingleOrDefaultAsync<MapPoint>(p => p.UnitId == unitId && p.Name.ToLower() == name.ToLower());
        }

        public async Task<MapPointDto> GetPointDto(string unitId, string name)
        {
            MapPoint point = await GetPoint(unitId, name);
            return mapper.Map<MapPointDto>(point);
        }

        public async Task<int> GetPointGroup(string unitId, string name)
        {
            return (await GetPoint(unitId, name)).GroupId;
        }

        public List<MapPointDto> GetPointsByGroupDtos(string unitId, int groupId)
        {
            List<MapPoint> points = databaseProvider.GetAll<MapPoint>(p => p.UnitId == unitId && p.GroupId == groupId);
            return mapper.Map<List<MapPointDto>>(points);
        }

        public List<MapPointDto> GetNearPointsDtos(string unitId, LocalMapPointDto dto)
        {
            List<MapPoint> points = databaseProvider.GetAll<MapPoint>(p => p.UnitId == unitId && p.Level == dto.Level);
            points = points.Where(p => MathAggregator.Distance(dto.X, dto.Y, dto.Z, p.X, p.Y, p.Z) <= dto.Distance).ToList();

            return mapper.Map<List<MapPointDto>>(points);
        }

        public async Task SetPoint(string unitId, MapPointDto pointDto)
        {
            MapPoint point = await GetPoint(unitId, pointDto.Name);

            if (point == null)
            {
                await CreatePoint(unitId, pointDto);
            }
            else
            {
                await UpdatePoint(unitId, point, pointDto);
            }
        }

        public async Task<bool> DeletePoint(string unitId, string name)
        {
            MapPoint point = await GetPoint(unitId, name);

            if(point == null)
            {
                return false;
            }

            databaseProvider.Delete(point);
            await databaseProvider.CommitAsync();

            return true;
        }

        private async Task CreatePoint(string unitId, MapPointDto pointDto)
        {
            MapPoint point = mapper.Map<MapPoint>(pointDto);
            point.UnitId = unitId;
            await databaseProvider.CreateAsync(point);
            await databaseProvider.CommitAsync();
        }

        private async Task UpdatePoint(string unitId, MapPoint point, MapPointDto newPointDto)
        {
            point = ObjectComparer.Merge(point, newPointDto,
                    u => u.Id,
                    u => u.Name,
                    u => u.UnitId,
                    u => u.CreatedDate
                );

            point.UnitId = unitId;

            databaseProvider.Update(point);
            await databaseProvider.CommitAsync();
        }
    }
}
