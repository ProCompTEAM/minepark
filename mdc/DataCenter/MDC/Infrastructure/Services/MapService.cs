using AutoMapper;
using MDC.Data.Dtos;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Utilities;
using System.Collections.Generic;
using System.Linq;

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

        public MapPoint GetPoint(string name)
        {
            string unitId = contextProvider.GetCurrentUnitId();
            return databaseProvider.SingleOrDefault<MapPoint>(p => p.UnitId == unitId && p.Name.ToLower() == name.ToLower());
        }

        public MapPointDto GetPointDto(string name)
        {
            MapPoint point = GetPoint(name);
            return mapper.Map<MapPointDto>(point);
        }

        public int GetPointGroup(string name)
        {
            return GetPoint(name).GroupId;
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
            points = points.Where(p => (int) MathAggregator.Distance(dto.X, dto.Y, dto.Z, p.X, p.Y, p.Z) <= dto.Distance).ToList();

            return mapper.Map<List<MapPointDto>>(points);
        }

        public void SetPoint(MapPointDto pointDto)
        {
            MapPoint point = GetPoint(pointDto.Name);

            if (point == null)
            {
                CreatePoint(pointDto);
            }
            else
            {
                UpdatePoint(point, pointDto);
            }
        }

        public bool DeletePoint(string name)
        {
            MapPoint point = GetPoint(name);

            if(point == null)
            {
                return false;
            }

            databaseProvider.Delete(point);
            databaseProvider.Commit();

            return true;
        }

        private void CreatePoint(MapPointDto pointDto)
        {
            MapPoint point = mapper.Map<MapPoint>(pointDto);
            point.UnitId = contextProvider.GetCurrentUnitId();
            databaseProvider.Create(point);
            databaseProvider.Commit();
        }

        private void UpdatePoint(MapPoint point, MapPointDto newPointDto)
        {
            point = ObjectComparer.Merge(point, newPointDto,
                    u => u.Id,
                    u => u.Name,
                    u => u.UnitId,
                    u => u.CreatedDate
                );

            point.UnitId = contextProvider.GetCurrentUnitId();

            databaseProvider.Update(point);
            databaseProvider.Commit();
        }
    }
}
