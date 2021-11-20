using MDC.Data.Enums;

namespace MDC.Data.Dtos.Realty
{
    public class RealtyAreaInfoDto
    {
        public string Name { get; set; }

        public RealtyAreaCategory Category { get; set; }

        public string World { get; set; }

        public double StartX { get; set; }

        public double StartZ { get; set; }

        public double EndX { get; set; }

        public double EndZ { get; set; }
    }
}
