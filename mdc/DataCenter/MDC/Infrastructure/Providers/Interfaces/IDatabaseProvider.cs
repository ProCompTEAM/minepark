using MDC.Data.Base;
using System;
using System.Collections.Generic;
using System.Linq.Expressions;

namespace MDC.Infrastructure.Providers.Interfaces
{
    public interface IDatabaseProvider
    {
        T GetById<T>(int id) where T : class, IEntity;

        List<T> GetAll<T>() where T : class, IEntity;

        List<T> GetAll<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        List<T2> GetAll<T1, T2>(Expression<Func<T1, T2>> selector) where T1 : class, IEntity;

        List<T2> GetAll<T1, T2>(Expression<Func<T1, T2>> selector, Expression<Func<T1, bool>> whereExp) where T1 : class, IEntity;

        T Single<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        T SingleOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        bool Any<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        int Count<T>() where T : class, IEntity;

        int Count<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        T First<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        T FirstOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        T Last<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        T LastOrDefault<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        void Create<T>(T entity) where T : class, IEntity;

        void Update<T>(T entity) where T : class, IEntity;

        void Delete<T>(T entity) where T : class, IEntity;

        void Delete<T>(Expression<Func<T, bool>> predicate) where T : class, IEntity;

        void Commit();
    }
}
