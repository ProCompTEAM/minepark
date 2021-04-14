using System.ComponentModel.DataAnnotations;
using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;

namespace MDC.Data.Models
{
    public class UnitBalance : BaseEntity, IUnited
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required]
        public double Balance { get; set; }
    }
}