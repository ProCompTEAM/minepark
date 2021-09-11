using System;

namespace MDC.Data.Dtos
{
    public class UserDto : IdentifiedDto
    {
        public string Name { get; set; }

        public string FullName { get; set; }

        public string Group { get; set; }

        public string People { get; set; }

        public string Tag { get; set; }

        public int Bonus { get; set; }

        public int MinutesPlayed { get; set; }

        public long PhoneNumber { get; set; }

        public bool Vip { get; set; }

        public bool Administrator { get; set; }

        public bool Builder { get; set; }

        public bool Realtor { get; set; }

        public PlayerBanDto Ban { get; set; }

        public DateTime JoinedDate { get; set; }

        public DateTime LeftDate { get; set; }

        public DateTime CreatedDate { get; set; }

        public DateTime UpdatedDate { get; set; }
    }
}
