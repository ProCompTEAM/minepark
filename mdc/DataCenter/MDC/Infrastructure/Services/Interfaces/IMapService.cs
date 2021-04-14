using MDC.Data.Dtos;
using MDC.Data.Models;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IMapService
    {
        Task<MapPoint> GetPoint(string unitId, string name);

        Task<MapPointDto> GetPointDto(string unitId, string name);

        Task<int> GetPointGroup(string unitId, string name);

        List<MapPointDto> GetPointsByGroupDtos(string unitId, int groupId);

        List<MapPointDto> GetNearPointsDtos(string unitId, LocalMapPointDto dto);

        Task SetPoint(string unitId, MapPointDto pointDto);

        Task<bool> DeletePoint(string unitId, string name);
    }
}
