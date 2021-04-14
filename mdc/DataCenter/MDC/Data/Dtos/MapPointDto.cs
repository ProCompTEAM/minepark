using System;

namespace MDC.Data.Dtos
{
    public class MapPointDto : PositionDto
    {
        public string Name { get; set; }

        public int GroupId { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}
