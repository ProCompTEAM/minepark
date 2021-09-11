using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;
using System;
using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models
{
    public class PlayerBan : BaseEntity
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UserName { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Issuer { get; set; }

        [Required]
        public DateTime End { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Reason { get; set; }
    }
}
