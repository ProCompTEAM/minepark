﻿namespace MDC.Infrastructure.Providers.Interfaces
{
    public interface IContextProvider
    {
        string Address { get; }

        string AccessToken { get; }

        void RestoreCredentials();

        bool Authorize();
    }
}
