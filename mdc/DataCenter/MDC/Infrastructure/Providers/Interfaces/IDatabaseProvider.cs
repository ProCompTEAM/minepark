using MDC.Data.Base;
using System;
using System.Collections.Generic;
using System.Linq.Expressions;
using System.Threading.Tasks;

namespace MDC.Infrastructure.Providers.Interfaces
{
    public interface IDatabaseProvider
    {
        T GetById<T>(int id) where T : class, IEntity;

        Task<T> FindPrimary<T>(int id) where T : class, IEntity;

        List<T> GetAll<T>() where T : class, IEntity;

        List<T> GetAll<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        List<T2> GetAll<T1, T2>(Expression<Func<T1, T2>> selector) where T1 : class, IEntity;

        List<T2> GetAll<T1, T2>(Expression<Func<T1, T2>> selector, Expression<Func<T1, bool>> whereExp) where T1 : class, IEntity;

        T Single<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        Task<T> SingleAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        T SingleOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        Task<T> SingleOrDefaultAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        bool Any<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        Task<bool> AnyAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        int Count<T>() where T : class, IEntity;

        Task<int> CountAsync<T>() where T : class, IEntity;

        int Count<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        Task<int> CountAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        long LongCount<T>() where T : class, IEntity;

        public Task<long> LongCountAsync<T>() where T : class, IEntity;

        T First<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        Task<T> FirstAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        T FirstOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        Task<T> FirstOrDefaultAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        T Last<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        Task<T> LastAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        T LastOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        Task<T> LastOrDefaultAsync<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        void Create<T>(T entity) where T : class, IEntity;

        Task CreateAsync<T>(T entity) where T : class, IEntity;

        void Update<T>(T entity) where T : class, IEntity;

        void Delete<T>(T entity) where T : class, IEntity;

        void Delete<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        int Commit();

        Task<int> CommitAsync();
    }
}
