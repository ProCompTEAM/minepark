using MinePark.Framework.Layers;
using MinePark.Framework.Layers.Base;

using System.Collections.Generic;
using System.Linq;

namespace MinePark.Framework
{
    public static class Store
    {
        static Store()
        {
            InitializeAll();
        }

        private static readonly List<Layer> layers = new List<Layer>();

        public static T GetLayer<T>() where T : Layer => (T)layers.Where(s => s.GetType() == typeof(T)).Single();

        public static void RegisterLayer(Layer layer) => layers.Add(layer);

        private static void InitializeAll()
        {
            RegisterLayer(new CommonLayer());
            RegisterLayer(new KeyboardLayer());
        }
    }
}
