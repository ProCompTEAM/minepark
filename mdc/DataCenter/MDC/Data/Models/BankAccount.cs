using System;
using System.ComponentModel.DataAnnotations;
using MDC.Common;
using MDC.Data.Enums;
using MDC.Data.Attributes;
using MDC.Data.Base;

namespace MDC.Data.Models
{
    public class BankAccount : BaseEntity, IUnited, ICreatedDate, IUpdatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Name { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required]
        public double Cash { get; set; }

        [Required]
        public double Debit { get; set; }

        [Required]
        public double Credit { get; set; }

        [Required]
        public PaymentMethod PaymentMethod { get; set; }

        public DateTime CreatedDate { get; set; }

        public DateTime UpdatedDate { get; set; }
    }
}