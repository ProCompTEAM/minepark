using MDC.Common.Network.HttpWeb;
using MDC.Common.Network.HttpWeb.Attributes;
using MDC.Data.Dtos;

using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;

using System.Threading.Tasks;

namespace MDC.Infrastructure.Controllers
{
    [WebRoute("web")]
    public class WebController
    {
        private readonly IWebService webService;

        public WebController(WebService webService)
        {
            this.webService = webService;
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
