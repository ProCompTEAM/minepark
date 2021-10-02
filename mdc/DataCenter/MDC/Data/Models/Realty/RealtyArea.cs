using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;
using MDC.Data.Enums;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models.Realty
{
    public class RealtyArea : BaseEntity, IUnited, ICreatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Name { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string World { get; set; }

        [Required]
        public virtual List<RealtyObject> Objects { get; set; }

        [Required]
        public RealtyAreaCategory Category { get; set; }

        [Required]
        public double StartX { get; set; }

        [Required]
        public double StartZ { get; set; }

        [Required]
        public double EndX { get; set; }

        [Required]
        public double EndZ { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}
