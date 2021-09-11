using AutoMapper;
using MDC.Data.Dtos;
using MDC.Data.Models;

namespace MDC.Common.Mapping
{
    public class MappingProfile : Profile
    {
        public MappingProfile()
        {
            CreateMap<User, UserDto>().ReverseMap();

            CreateMap<UserSettings, UserSettingsDto>().ReverseMap();

            CreateMap<MapPoint, MapPointDto>().ReverseMap();

            CreateMap<FloatingText, FloatingTextDto>().ReverseMap();

            CreateMap<Credentials, CredentialsDto>().ReverseMap();

            CreateMap<PlayerBan, PlayerBanDto>().ReverseMap();
        }
    }
}
