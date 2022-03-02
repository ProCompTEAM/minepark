using BoDi;
using MDC.Common.Mapping;
using MDC.Infrastructure.Controllers;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Services;
using MDC.Infrastructure.Services.Audit;

namespace MDC.Infrastructure
{
    public static class Resolver
    {
        public static IObjectContainer Container { private set; get; }

        public static void ResolveAll()
        {
            Container = new ObjectContainer();

            ResolveMapper();

            ResolveProviders();
            ResolveServices();
            ResolveControllers();
        }

        private static void ResolveControllers()
        {
            Resolve<SettingsController>();
            Resolve<PhonesController>();
            Resolve<UsersController>();
            Resolve<MapController>();
            Resolve<BankingController>();
            Resolve<FloatingTextsController>();
            Resolve<WebController>();
            Resolve<BanRecordsController>();
        }

        private static void ResolveProviders()
        {
            Resolve<DateTimeProvider>();
            Resolve<TokenProvider>();
            Resolve<DatabaseProvider>();
            Resolve<AuthorizationProvider>();
        }

        private static void ResolveServices()
        {
            Resolve<PhonesService>();
            Resolve<BankingService>();
            Resolve<BanRecordsService>();
            Resolve<UsersService>();
            Resolve<MapService>();
            Resolve<FloatingTextsService>();
            Resolve<TokenService>();
            Resolve<WebService>();

            Resolve<MoneyTransactionsAuditService>();
            Resolve<ExecutedCommandsAuditService>();
            Resolve<ChatMessagesAuditService>();
            Resolve<UserTrafficAuditService>();
        }

        private static void ResolveMapper()
        {
            Container.RegisterInstanceAs(CommonMapper.Instance);
        }

        private static void Resolve<T>()
        {
            Container.Resolve<T>();
        }
    }
}
