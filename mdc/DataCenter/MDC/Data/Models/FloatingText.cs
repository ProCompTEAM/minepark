using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;
using System;
using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models
{
    public class FloatingText : BaseEntity, IUnited, ICreatedDate
    {
        [Required, Unicode(Defaults.DefaultLongStringLength)]
        public string Text { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Level { get; set; }

        [Required]
        public int X { get; set; }

        [Required]
        public int Y { get; set; }

        [Required]
        public int Z { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}