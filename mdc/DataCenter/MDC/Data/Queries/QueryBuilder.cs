using MDC.Data.Base;
using System;
using System.Linq.Expressions;

namespace MDC.Data.Queries
{
    public class QueryBuilder<T> where T : class, IEntity
    {
        public Expression<Func<T, bool>> Query { get; protected set; }

        public QueryBuilder()
        {
            Query = exp => true;
        }

        public QueryBuilder(Expression<Func<T, bool>> query)
        {
            Query = query;
        }

        public void And(Expression<Func<T, bool>> query)
        {
            Query = Expression.Lambda<Func<T, bool>>(Expression.AndAlso(Query.Body, Expression.Invoke(query, Query.Parameters[0])), Query.Parameters[0]);
        }
    }
}
