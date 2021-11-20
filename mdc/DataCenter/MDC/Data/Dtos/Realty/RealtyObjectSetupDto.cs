using MDC.Data.Enums;

namespace MDC.Data.Dtos.Realty
{
    public class RealtyObjectSetupDto
    {
        public string Name { get; set; }

        public string UnitId { get; set; }

        public string AreaName { get; set; }

        public RealtyType Type { get; set; }

        public double Price { get; set; }

        public double StartX { get; set; }

        public double StartY { get; set; }

        public double StartZ { get; set; }

        public double EndX { get; set; }

        public double EndY { get; set; }

        public double EndZ { get; set; }
    }
}
