using MDC.Common.Network.HttpWeb;
using MDC.Common.Network.HttpWeb.Attributes;

using MDC.Data.Dtos;

using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;

using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    [WebRoute("map")]
    public class MapController
    {
        private readonly IMapService mapService;

        public MapController(MapService mapService)
        {
            this.mapService = mapService;
        }

        public async Task<MapPointDto> GetPoint(string name, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await mapService.GetPointDto(unitId, name);
        }

        public async Task<int> GetPointGroup(string name, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await mapService.GetPointGroup(unitId, name);
        }

        public List<MapPointDto> GetPointsByGroup(int groupId, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return mapService.GetPointsByGroupDtos(unitId, groupId);
        }

        public List<MapPointDto> GetNearPoints(LocalMapPointDto dto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return mapService.GetNearPointsDtos(unitId, dto);
        }

        public async Task SetPoint(MapPointDto pointDto, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            await mapService.SetPoint(unitId, pointDto);
        }

        public async Task<bool> DeletePoint(string name, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await mapService.DeletePoint(unitId, name);
        }
    }
}
