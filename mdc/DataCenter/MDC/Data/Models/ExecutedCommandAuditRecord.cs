using System;
using System.ComponentModel.DataAnnotations;
using MDC.Common;
using MDC.Data.Enums;
using MDC.Data.Attributes;
using MDC.Data.Base;

namespace MDC.Data.Models
{
    public class ExecutedCommandAuditRecord : BaseEntity, IUnited, ICreatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Subject { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Command { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}