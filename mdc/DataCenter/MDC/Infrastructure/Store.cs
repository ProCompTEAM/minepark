using AutoMapper;
using MDC.Common.Mapping;
using MDC.Infrastructure.Controllers;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Interfaces;
using System.Collections.Generic;
using System.Linq;

namespace MDC.Infrastructure
{
    public static class Store
    {
        private static readonly List<IController> controllers = new List<IController>();

        private static readonly List<IProvider> providers = new List<IProvider>();

        private static readonly List<IService> services = new List<IService>();

        public static void InitializeAll()
        {
            InitializeProviders();
            InitializeServices();
            InitializeControllers();
        }

        public static T GetController<T>() where T : IController => (T)controllers.Where(c => c.GetType() == typeof(T)).Single();

        public static IController GetControllerByRoute(string route) => controllers.Where(c => c.Route == route).SingleOrDefault();

        public static T GetProvider<T>() where T : IProvider => (T)providers.Where(p => p.GetType() == typeof(T)).Single();

        public static T GetService<T>() where T : IService => (T)services.Where(s => s.GetType() == typeof(T)).Single();

        public static Mapper GetMapper() => CommonMapper.Instance;

        public static void RegisterController(IController controller) => controllers.Add(controller);

        public static void RegisterProvider(IProvider provider) => providers.Add(provider);

        public static void RegisterService(IService service) => services.Add(service);

        private static void InitializeControllers()
        {
            RegisterController(new SettingsController());
            RegisterController(new PhonesController());
            RegisterController(new UsersController());
            RegisterController(new MapController());
            RegisterController(new BankingController());
        }

        private static void InitializeProviders()
        {
            RegisterProvider(new DateTimeProvider());
            RegisterProvider(new TokenProvider());
            RegisterProvider(new DatabaseProvider());
            RegisterProvider(new ContextProvider());
        }

        private static void InitializeServices()
        {
            RegisterService(new PhonesService());
            RegisterService(new BankingService());
            RegisterService(new UsersService());
            RegisterService(new MapService());
        }
    }
}
