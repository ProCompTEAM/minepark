using System;

namespace MDC.Utilities
{
    public static class MathAggregator
    {
        public static double Distance(double x1, double y1, double z1, double x2, double y2, double z2)
        {
            return Math.Sqrt(Math.Pow(x2 - x1, 2) + Math.Pow(y2 - y1, 2) + Math.Pow(z2 - z1, 2));
        }

        public static bool Intersect(double minimalX, double minimalZ, double targetX, double targetZ, double maximalX, double maximalZ)
        {
            return minimalX <= targetX && maximalX >= targetX && minimalZ <= targetZ && maximalZ >= targetZ;
        }

        public static bool Intersect(double minimalX, double minimalY, double minimalZ, double targetX, double targetY, double targetZ, double maximalX, double maximalY, double maximalZ)
        {
            return minimalX <= targetX && maximalX >= targetX && minimalY <= targetY && maximalY >= targetY && minimalZ <= targetZ && maximalZ >= targetZ;
        }
    }
}
