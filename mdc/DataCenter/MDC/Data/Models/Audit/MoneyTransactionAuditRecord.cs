using System;
using System.ComponentModel.DataAnnotations;
using MDC.Common;
using MDC.Data.Enums;
using MDC.Data.Attributes;
using MDC.Data.Base;

namespace MDC.Data.Models.Audit
{
    public class MoneyTransactionAuditRecord : BaseEntity, IUnited, ICreatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Subject { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required]
        public double Amount { get; set; }

        [Required]
        public TransactionType TransactionType { get; set; }

        [Required]
        public PaymentMethod TargetAccount { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}