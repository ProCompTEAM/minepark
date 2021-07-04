using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;
using System;
using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models
{
    public class MapPoint : BaseEntity, IUnited, ICreatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Name { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string World { get; set; }

        [Required]
        public double X { get; set; }

        [Required]
        public double Y { get; set; }

        [Required]
        public double Z { get; set; }

        [Required]
        public int GroupId { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}