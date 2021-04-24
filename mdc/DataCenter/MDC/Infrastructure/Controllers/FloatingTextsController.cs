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
    public class FloatingTextsController : IController
    {
        public string Route { get; set; } = "floating-texts";

        private readonly IUnitProvider unitProvider;

        private readonly IFloatingTextsService floatingTextsService;

        public FloatingTextsController()
        {
            unitProvider = Store.GetProvider<UnitProvider>();
            floatingTextsService = Store.GetService<FloatingTextsService>();
        }

        public List<FloatingTextDto> GetAll(RequestContext context)
        {
            string unitId = unitProvider.GetCurrentUnitId(context.AccessToken);
            return floatingTextsService.GetAll(unitId);
        }

        public async Task<FloatingTextDto> Save(LocalFloatingTextDto floatingTextData, RequestContext context)
        {
            string unitId = unitProvider.GetCurrentUnitId(context.AccessToken);
            return await floatingTextsService.Save(unitId, floatingTextData);
        }

        public async Task<bool> Remove(PositionDto position, RequestContext context)
        {
            string unitId = unitProvider.GetCurrentUnitId(context.AccessToken);
            return await floatingTextsService.Remove(unitId, position.Level, position.X, position.Y, position.Z);
        }
    }
}