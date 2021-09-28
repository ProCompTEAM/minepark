using MDC.Common.Network.HttpWeb;
using MDC.Data.Dtos;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    public class WebController : IController
    {
        public string Route { get; set; } = "web";

        private readonly IWebService webService;

        public WebController()
        {
            webService = Store.GetService<WebService>();
        }

        public async Task<UserWebProfileDto> GetUserProfile(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await webService.GetUserProfile(unitId, userName);
        }

        public async Task<string> GetPassword(string userName)
        {
            return await webService.GetPassword(userName);
        }
    }
}
