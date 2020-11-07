using System;

namespace MDC.Infrastructure.Generic.Interfaces
{
    public interface ILogger
    {
        void Info(string message, string prefix, ConsoleColor color);
    }
}