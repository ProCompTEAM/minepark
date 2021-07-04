using MDC.Common;

namespace MDC.Loader
{
    class CommonMDCLoader
    {
        static void Main()
        {
            General.LoadAll();

            General.Log($"Using Protocol version {Protocol.Version}");
            General.Log($"Done. {General.ProductName}, version: {General.Version}");
        }
    }
}
