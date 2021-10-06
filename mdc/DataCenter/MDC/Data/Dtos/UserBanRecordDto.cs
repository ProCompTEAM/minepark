using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace MDC.Data.Dtos
{
    public class UserBanRecordDto
    {
        public string UserName { get; set; }

        public string IssuerName { get; set; }

        public DateTime ReleaseDate { get; set; }

        public string Reason { get; set; }
    }
}
