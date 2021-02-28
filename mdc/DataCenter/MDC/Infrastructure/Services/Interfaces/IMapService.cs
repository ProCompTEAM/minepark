using MDC.Data.Dtos;
using MDC.Data.Models;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IMapService
    {
        Task<MapPoint> GetPoint(string name);

        Task<MapPointDto> GetPointDto(string name);

        Task<int> GetPointGroup(string name);

        List<MapPointDto> GetPointsByGroupDtos(int groupId);

        List<MapPointDto> GetNearPointsDtos(LocalMapPointDto dto);

        Task SetPoint(MapPointDto pointDto);

        Task<bool> DeletePoint(string name);
    }
}
