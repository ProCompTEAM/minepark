using System;
using System.ComponentModel.DataAnnotations;
using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;
using MDC.Data.Enums;

namespace MDC.Data.Models.Audit
{
    public class UserTrafficAuditRecord : BaseEntity, IUnited, ICreatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Subject { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required]
        public UserTrafficType UserTrafficType { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}