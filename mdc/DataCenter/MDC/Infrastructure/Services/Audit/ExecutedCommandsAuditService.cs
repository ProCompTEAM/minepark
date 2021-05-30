﻿using MDC.Infrastructure.Services.Audit.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using System.Threading.Tasks;
using MDC.Data.Models.Audit;
using MDC.Common;

namespace MDC.Infrastructure.Services.Audit
{
    public class ExecutedCommandsAuditService : IExecutedCommandsAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public ExecutedCommandsAuditService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
        }

        public async Task SaveExecutedCommandAuditRecord(string unitId, string userName, string command)
        {
            if (command.Length > Defaults.DefaultStringLength)
            {
                command = StringUtility.CutWithEnding(command, Defaults.DefaultStringLength);
            }

            ExecutedCommandAuditRecord executedCommandAuditRecord = new ExecutedCommandAuditRecord
            {
                Subject = userName,
                UnitId = unitId,
                Command = command
            };

            await databaseProvider.CreateAsync(executedCommandAuditRecord);
            await databaseProvider.CommitAsync();
        }
    }
}