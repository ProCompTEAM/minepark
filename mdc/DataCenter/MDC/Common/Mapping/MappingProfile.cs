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
        }
    }
}
