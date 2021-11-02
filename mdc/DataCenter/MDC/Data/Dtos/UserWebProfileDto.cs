using MDC.Data.Enums;
using System;

namespace MDC.Data.Dtos
{
    public class UserWebProfileDto
    {
        public string FullName { get; set; }

        public UserPrivilege Privilege { get; set; }

        public long PhoneNumber { get; set; }

        public double MoneySummary { get; set; }

        public int MinutesPlayed { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}