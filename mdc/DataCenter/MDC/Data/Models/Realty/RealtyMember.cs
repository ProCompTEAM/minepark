using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;

using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models.Realty
{
    public class RealtyMember : BaseEntity, IUnited
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Name { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required]
        public virtual RealtyObject Parent { get; set; }
    }
}
