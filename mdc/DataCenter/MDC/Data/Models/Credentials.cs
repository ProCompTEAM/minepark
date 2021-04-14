using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;
using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models
{
    public class Credentials : BaseEntity
    {
        [Required, Unicode(36)]
        public string GeneratedToken { get; set; }

        [Unicode(Defaults.DefaultLongStringLength)]
        public string Tag { get; set; }
    }
}