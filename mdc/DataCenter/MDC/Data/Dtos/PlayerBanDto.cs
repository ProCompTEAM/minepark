using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace MDC.Data.Dtos
{
    public class PlayerBanDto
    {
        public string UserName { get; set; }

        public string Issuer { get; set; }

        public DateTime End { get; set; }

        public string Reason { get; set; }
    }
}
