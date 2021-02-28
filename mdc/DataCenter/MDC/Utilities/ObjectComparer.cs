using System;
using System.Collections.Generic;
using System.Linq;
using System.Linq.Expressions;
using System.Reflection;

namespace MDC.Utilities
{
    public static class ObjectComparer
    {
        public static T1 Merge<T1, T2>(T1 target, T2 source, string[] exceptions)
        {
            List<PropertyInfo> targetProperties = typeof(T1).GetProperties(BindingFlags.Public | BindingFlags.Instance).ToList();
            List<PropertyInfo> sourceProperties = typeof(T2).GetProperties(BindingFlags.Public | BindingFlags.Instance).ToList();

            targetProperties = targetProperties.Where(p => !exceptions.Contains(p.Name)).ToList();

            foreach (var targetProperty in targetProperties)
            {
                PropertyInfo sourceProperty = sourceProperties.SingleOrDefault(p => p.Name == targetProperty.Name);
                
                if(sourceProperty != null)
                {
                    object newValue = sourceProperty.GetValue(source);
                    targetProperty.SetValue(target, newValue);
                }
            }

            return target;
        }

        public static T1 Merge<T1, T2>(T1 target, T2 source, params Expression<Func<T1, object>>[] exceptions)
        {
            string[] exceptedStrings = exceptions.Select(exp =>
            {
                MemberExpression memberExpression = exp.Body as MemberExpression ?? ((UnaryExpression)exp.Body).Operand as MemberExpression;
                return memberExpression.Member.Name;
            }).ToArray();

            return Merge(target, source, exceptedStrings);
        }
    }
}
