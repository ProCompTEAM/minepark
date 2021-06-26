using MDC.Common.Network.HttpWeb;
using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    public class FloatingTextsController : IController
    {
        public string Route { get; set; } = "floating-texts";

        private readonly IFloatingTextsService floatingTextsService;

        public FloatingTextsController()
        {
            floatingTextsService = Store.GetService<FloatingTextsService>();
        }

        public List<FloatingTextDto> GetAll(RequestContext context)
        {
            string unitId = context.UnitId;
            return floatingTextsService.GetAll(unitId);
        }

        public async Task<FloatingTextDto> Save(LocalFloatingTextDto floatingTextData, RequestContext context)
        {
            string unitId = context.UnitId;
            return await floatingTextsService.Save(unitId, floatingTextData);
        }

        public async Task<bool> Remove(PositionDto position, RequestContext context)
        {
            string unitId = context.UnitId;
            return await floatingTextsService.Remove(unitId, position.Level, position.X, position.Y, position.Z);
        }
    }
}