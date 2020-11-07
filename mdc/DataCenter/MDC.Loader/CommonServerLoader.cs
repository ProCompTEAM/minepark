namespace MDC.Loader
{
    class CommonMDCLoader
    {
        static void Main(string[] args)
        {
            General.LoadAll();

            General.Log($"Done. {General.ProductName}, version: {General.Version}");
        }
    }
}
