using MDC.Common.Network.HttpWeb;
using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    public class MapController : IController
    {
        public string Route { get; set; } = "map";

        private readonly IUnitProvider unitProvider;

        private readonly IMapService mapService;

        public MapController()
        {
            unitProvider = Store.GetProvider<UnitProvider>();
            mapService = Store.GetService<MapService>();
        }

        public async Task<MapPointDto> GetPoint(string name, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await mapService.GetPointDto(unitId, name);
        }

        public async Task<int> GetPointGroup(string name, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await mapService.GetPointGroup(unitId, name);
        }

        public List<MapPointDto> GetPointsByGroup(int groupId, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return mapService.GetPointsByGroupDtos(unitId, groupId);
        }

        public List<MapPointDto> GetNearPoints(LocalMapPointDto dto, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return mapService.GetNearPointsDtos(unitId, dto);
        }

        public async Task SetPoint(MapPointDto pointDto, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            await mapService.SetPoint(unitId, pointDto);
        }

        public async Task<bool> DeletePoint(string name, RequestContext requestContext)
        {
            string unitId = unitProvider.GetCurrentUnitId(requestContext.AccessToken);
            return await mapService.DeletePoint(unitId, name);
        }
    }
}
