using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;

using System;
using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models
{
    public class User : BaseEntity, ICreatedDate, IUpdatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Name { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string FullName { get; set; }

        [Unicode(Defaults.DefaultLongStringLength)]
        public string Password { get; set; }

        [Unicode(Defaults.DefaultStringLength)]
        public string Email { get; set; }

        [Unicode(Defaults.DefaultStringLength)]
        public string Group { get; set; }

        [Unicode(Defaults.DefaultLongStringLength)]
        public string People { get; set; }

        [Unicode(Defaults.DefaultLongStringLength)]
        public string Tag { get; set; }

        [Required]
        public int Bonus { get; set; }

        [Required]
        public int MinutesPlayed { get; set; }

        [Required]
        public bool Vip { get; set; }

        [Required]
        public bool Administrator { get; set; }

        [Required]
        public bool Builder { get; set; }

        [Required]
        public bool Realtor { get; set; }

        public virtual PlayerBan Ban { get; set; }

        public DateTime JoinedDate { get; set; }

        public DateTime LeftDate { get; set; }

        public DateTime CreatedDate { get; set; }

        public DateTime UpdatedDate { get; set; }
    }
}