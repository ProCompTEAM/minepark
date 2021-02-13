using MDC.Infrastructure.Tasks.Interfaces;
using System.Collections.Generic;
using System.Linq;

namespace MDC.Infrastructure
{
    public static class Scheduler
    {
        private static readonly List<ITask> tasks = new List<ITask>();

        public static void InitializeTasks()
        {
            //TODO
        }

        public static T GetTask<T>() where T : ITask => (T) tasks.Where(c => c.GetType() == typeof(T)).Single();
    }
}
