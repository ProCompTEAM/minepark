using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;

namespace MDC.Infrastructure.Controllers
{
    public class PhonesController : IController
    {
        public string Route { get; set; } = "phones";

        private readonly IPhonesService phonesService;

        public PhonesController()
        {
            phonesService = Store.GetService<PhonesService>();
        }

        public long GetNumberForOrganization(string organizationName)
        {
            return phonesService.GetNumberForOrganization(organizationName);
        }

        public long CreateNumberForOrganization(string organizationName)
        {
            return phonesService.CreateNumberForOrganization(organizationName);
        }
    }
}
