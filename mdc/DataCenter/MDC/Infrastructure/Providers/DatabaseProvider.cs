using MDC.Data;
using MDC.Data.Base;
using MDC.Infrastructure.Providers.Interfaces;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Linq.Expressions;

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

        public T Single<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().Single(predicate);

        public T SingleOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().SingleOrDefault(predicate);

        public T First<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().First(predicate);

        public T FirstOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().FirstOrDefault(predicate);

        public T Last<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().Last(predicate);

        public T LastOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().LastOrDefault(predicate);

        public bool Null<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().Count(predicate) < 1;

        public int Count<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity => Database.Context.Set<T>().Count(predicate);

        public void Create<T>(T entity) where T : class, IEntity
        {
            if (entity is ICreatedDate)
            {
                ((ICreatedDate)entity).CreatedDate = dateTimeProvider.Now;
            }

            Database.Context.Add(entity);
        }

        public void Update<T>(T entity) where T : class, IEntity
        {
            if(entity is IUpdatedDate)
            {
                ((IUpdatedDate)entity).UpdatedDate = dateTimeProvider.Now;
            }

            Database.Context.Update(entity);
        }

        public void Delete<T>(T entity) where T : class, IEntity => Database.Context.Set<T>().Remove(entity);

        public void Delete<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity
        {
            List<T> entities = GetAll(predicate);
            Database.Context.Set<T>().RemoveRange(entities);
        }

        public void Commit() => Database.Context.SaveChanges();
    }
}
