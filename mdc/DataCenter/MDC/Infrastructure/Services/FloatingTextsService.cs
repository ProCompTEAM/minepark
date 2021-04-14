using AutoMapper;
using MDC.Data.Dtos;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using System;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Utilities;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Services
{
    public class FloatingTextsService : IFloatingTextsService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IMapper mapper;

        public FloatingTextsService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
            mapper = Store.GetMapper();
        }

        public List<FloatingTextDto> GetAll(string unitId)
        {
            List<FloatingText> floatingTexts = databaseProvider.GetAll<FloatingText>(t => t.UnitId == unitId);

            return mapper.Map<List<FloatingTextDto>>(floatingTexts);
        }

        public async Task Save(string unitId, string text, string level, double x, double y, double z)
        {
            FloatingText floatingText = await GetFloatingText(unitId, level, x, y, z);

            if (floatingText == null)
            {
                Create(unitId, text, level, x, y, z);
                return;
            }

            Update(floatingText, text);
        }

        public async Task<bool> Remove(string unitId, string level, double x, double y, double z)
        {
            FloatingText floatingText = await GetFloatingText(unitId, level, x, y, z);

            if (floatingText == null)
            {
                return false;
            }

            databaseProvider.Delete(floatingText);
            await databaseProvider.CommitAsync();

            return true;
        }

        private FloatingText GetFloatingTextTemplate(string unitId, string text, string level, double x, double y, double z)
        {
            return new FloatingText
            {
                UnitId = unitId,
                Text = text,
                Level = level,
                X = x,
                Y = y,
                Z = z
            };
        }

        private async Task<FloatingText> GetFloatingText(string unitId, string level, double x, double y, double z)
        {
            return await databaseProvider.SingleOrDefaultAsync<FloatingText>(t =>
                t.X == x &&  t.Y == y && t.Z == z && t.Level == level 
                    && t.UnitId == unitId);
        }

        private async Task<bool> Exists(string unitId, string level, double x, double y, double z)
        {
            return await databaseProvider.AnyAsync<FloatingText>(t => 
                t.X == x &&  t.Y == y && t.Z == z && t.Level == level 
                    && t.UnitId == unitId);
        }

        private async void Update(FloatingText floatingText, string text)
        {
            floatingText.Text = text;

            databaseProvider.Update(floatingText);
            await databaseProvider.CommitAsync();
        }

        private async void Create(string unitId, string text, string level, double x, double y, double z)
        {
            FloatingText floatingText = GetFloatingTextTemplate(unitId, text, level, x, y, z);

            await databaseProvider.CreateAsync(floatingText);
            await databaseProvider.CommitAsync();
        }
    }
}
