using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Collections.Generic;

namespace MDC.Infrastructure.Controllers
{
    public class MapController : IController
    {
        public string Route { get; set; } = "map";

        private readonly IMapService mapService;

        public MapController()
        {
            mapService = Store.GetService<MapService>();
        }

        public MapPointDto GetPoint(string name)
        {
            return mapService.GetPointDto(name);
        }

        public int GetPointGroup(string name)
        {
            return mapService.GetPointGroup(name);
        }

        public List<MapPointDto> GetPointsByGroup(int groupId)
        {
            return mapService.GetPointsByGroupDtos(groupId);
        }

        public List<MapPointDto> GetNearPoints(LocalMapPointDto dto)
        {
            return mapService.GetNearPointsDtos(dto);
        }

        public void SetPoint(MapPointDto pointDto)
        {
            mapService.SetPoint(pointDto);
        }

        public bool DeletePoint(string name)
        {
            return mapService.DeletePoint(name);
        }
    }
}
