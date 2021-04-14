using AutoMapper;

namespace MDC.Common.Mapping
{
    public static class CommonMapper
    {
        public static Mapper Instance { private set; get; }

        public static void Initialize()
        {
            Instance = new Mapper(CreateConfiguration());
        }

        private static MapperConfiguration CreateConfiguration()
        {
            MapperConfiguration config = new MapperConfiguration(cfg =>
            {
                cfg.AddProfile(new MappingProfile());
            });

            return config;
        }
    }
}
