using System;

namespace MDC.Data.Dtos
{
    public class MapPointDto
    {
        public string Name { get; set; }

        public int GroupId { get; set; }

        public string World { get; set; }

        public double X { get; set; }

        public double Y { get; set; }

        public double Z { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}
