using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;

using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models.Realty
{
    public class RealtyObject : BaseEntity, IUnited, ICreatedDate, IUpdatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Name { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Assigned { get; set; }

        [Required]
        public virtual RealtyArea Area { get; set; }

        [Required]
        public virtual List<RealtyMember> Members { get; set; }

        [Required]
        public double StartX { get; set; }

        [Required]
        public double StartY { get; set; }

        [Required]
        public double StartZ { get; set; }

        [Required]
        public double EndX { get; set; }

        [Required]
        public double EndY { get; set; }

        [Required]
        public double EndZ { get; set; }

        [Required]
        public double Price { get; set; }

        public int DaysBorder { get; set; }

        public int DaysAvailable { get; set; }

        [Required]
        public bool Rental { get; set; }

        [Required]
        public bool AllowBuild { get; set; }

        [Required]
        public bool AllowMembers { get; set; }

        public DateTime RentedDate { get; set; }

        public DateTime CreatedDate { get; set; }

        public DateTime UpdatedDate { get; set; }
    }
}
