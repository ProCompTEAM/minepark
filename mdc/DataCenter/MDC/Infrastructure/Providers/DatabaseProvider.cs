using MDC.Data;
using MDC.Data.Base;
using MDC.Infrastructure.Providers.Interfaces;
using Microsoft.EntityFrameworkCore;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Linq.Expressions;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Providers
{
    public class DatabaseProvider : IDatabaseProvider, IProvider
    {
        private readonly IDateTimeProvider dateTimeProvider;

        public DatabaseProvider()
        {
            dateTimeProvider = Store.GetProvider<DateTimeProvider>();
        }

        public List<T> GetAll<T>() where T : class, IEntity => Database.Context.Set<T>().ToList();

        public List<T> GetAll<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().Where(predicate).ToList();

        public List<T2> GetAll<T1, T2>(Expression<Func<T1, T2>> selector) where T1 : class, IEntity => Database.Context.Set<T1>().Select(selector).ToList();

        public List<T2> GetAll<T1, T2>(Expression<Func<T1, T2>> selector, Expression<Func<T1, bool>> whereExp) where T1 : class, IEntity
            => Database.Context.Set<T1>().Where(whereExp).Select(selector).ToList();

        public T GetById<T>(int id) where T : class, IEntity => Database.Context.Set<T>().SingleOrDefault(e => e.Id == id);

        public async Task<T> FindPrimary<T>(int id) where T : class, IEntity => await Database.Context.Set<T>().FindAsync(id);

        public T Single<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().Single(predicate);

        public Task<T> SingleAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().SingleAsync(predicate);

        public T SingleOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().SingleOrDefault(predicate);

        public Task<T> SingleOrDefaultAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().SingleOrDefaultAsync(predicate);

        public T First<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().First(predicate);

        public Task<T> FirstAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().FirstAsync(predicate);

        public T FirstOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().FirstOrDefault(predicate);

        public Task<T> FirstOrDefaultAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().FirstOrDefaultAsync(predicate);

        public T Last<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().Last(predicate);

        public Task<T> LastAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().LastAsync(predicate);

        public T LastOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().LastOrDefault(predicate);

        public Task<T> LastOrDefaultAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().LastOrDefaultAsync(predicate);

        public bool Any<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().Any(predicate);

        public Task<bool> AnyAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().AnyAsync(predicate);

        public int Count<T>() where T : class, IEntity => Database.Context.Set<T>().Count();

        public Task<int> CountAsync<T>() where T : class, IEntity => Database.Context.Set<T>().CountAsync();

        public int Count<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().Count(predicate);

        public Task<int> CountAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().CountAsync(predicate);

        public long LongCount<T>() where T : class, IEntity => Database.Context.Set<T>().LongCount();

        public Task<long> LongCountAsync<T>() where T : class, IEntity => Database.Context.Set<T>().LongCountAsync();

        public void Create<T>(T entity) where T : class, IEntity
        {
            RegisterDateTime(entity);
            Database.Context.Add(entity);
        }

        public async Task CreateAsync<T>(T entity) where T : class, IEntity
        {
            RegisterDateTime(entity);
            await Database.Context.AddAsync(entity);
        }

        public void Update<T>(T entity) where T : class, IEntity
        {
            if (entity is IUpdatedDate createdEntity)
            {
                createdEntity.UpdatedDate = dateTimeProvider.Now;
            }

            Database.Context.Update(entity);
        }

        public void Delete<T>(T entity) where T : class, IEntity => Database.Context.Set<T>().Remove(entity);

        public void Delete<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity
        {
            List<T> entities = GetAll(predicate);
            Database.Context.Set<T>().RemoveRange(entities);
        }

        public int Commit() => Database.Context.SaveChanges();

        public async Task<int> CommitAsync() => await Database.Context.SaveChangesAsync();

        private void RegisterDateTime<T>(T entity) where T : class, IEntity
        {
            if (entity is ICreatedDate createdEntity)
            {
                createdEntity.CreatedDate = dateTimeProvider.Now;
            }

            if (entity is IUpdatedDate updatedEntity)
            {
                updatedEntity.UpdatedDate = dateTimeProvider.Now;
            }
        }
    }
}
