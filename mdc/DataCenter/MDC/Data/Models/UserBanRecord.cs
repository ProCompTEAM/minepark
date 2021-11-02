using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;
using System;
using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models
{
    public class UserBanRecord : BaseEntity
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UserName { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string IssuerName { get; set; }

        [Required]
        public DateTime ReleaseDate { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Reason { get; set; }
    }
}
