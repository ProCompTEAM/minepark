using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;
using System;
using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models
{
    public class Bank : BaseEntity, IUnited, ICreatedDate, IUpdatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Name { get; set; }

        [Required]
        public string UnitId { get; set; }

        [Required]
        public double Cash { get; set; }

        [Required]
        public double Debit { get; set; }

        [Required]
        public double Credit { get; set; }

        public DateTime CreatedDate { get; set; }

        public DateTime UpdatedDate { get; set; }
    }
}