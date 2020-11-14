using MDC.Data.Dtos;
using MDC.Data.Models;
using System.Collections.Generic;

namespace MDC.Infrastructure.Services.Interfaces
{
    public interface IMapService
    {
        MapPoint GetPoint(string name);

        MapPointDto GetPointDto(string name);

        int GetPointGroup(string name);

        List<MapPointDto> GetPointsByGroupDtos(int groupId);

        List<MapPointDto> GetNearPointsDtos(LocalMapPointDto dto);

        void SetPoint(MapPointDto pointDto);

        bool DeletePoint(string name);
    }
}
