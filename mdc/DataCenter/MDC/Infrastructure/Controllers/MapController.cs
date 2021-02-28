using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Collections.Generic;
using System.Threading.Tasks;

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

        public async Task<MapPointDto> GetPoint(string name)
        {
            return await mapService.GetPointDto(name);
        }

        public async Task<int> GetPointGroup(string name)
        {
            return await mapService.GetPointGroup(name);
        }

        public List<MapPointDto> GetPointsByGroup(int groupId)
        {
            return mapService.GetPointsByGroupDtos(groupId);
        }

        public List<MapPointDto> GetNearPoints(LocalMapPointDto dto)
        {
            return mapService.GetNearPointsDtos(dto);
        }

        public async Task SetPoint(MapPointDto pointDto)
        {
            await mapService.SetPoint(pointDto);
        }

        public async Task<bool> DeletePoint(string name)
        {
            return await mapService.DeletePoint(name);
        }
    }
}
