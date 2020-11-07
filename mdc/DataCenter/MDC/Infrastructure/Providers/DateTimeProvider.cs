using MDC.Infrastructure.Providers.Interfaces;
using System;

namespace MDC.Infrastructure.Providers
{
    public class DateTimeProvider : IDateTimeProvider, IProvider
    {
        public DateTime Now => DateTime.Now;
    }
}
