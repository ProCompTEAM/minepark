using System;

namespace MDC.Infrastructure.Providers.Interfaces
{
    public interface IDateTimeProvider
    {
        DateTime Now { get; }
    }
}
