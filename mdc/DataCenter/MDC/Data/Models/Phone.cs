using MDC.Common;
using MDC.Data.Attributes;
using MDC.Data.Base;
using MDC.Data.Enums;
using System;
using System.ComponentModel.DataAnnotations;

namespace MDC.Data.Models
{
    public class Phone : BaseEntity, ICreatedDate, IUpdatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Subject { get; set; }

        [Required]
        public long Number { get; set; }

        [Required]
        public PhoneSubjectType SubjectType { get; set; }

        public DateTime CreatedDate { get; set; }

        public DateTime UpdatedDate { get; set; }
    }
}