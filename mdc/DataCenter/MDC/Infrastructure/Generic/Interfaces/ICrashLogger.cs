namespace MDC.Infrastructure.Generic.Interfaces
{
    public interface ICrashLogger
    {
        void Crash(string description, string[] traces);
    }
}