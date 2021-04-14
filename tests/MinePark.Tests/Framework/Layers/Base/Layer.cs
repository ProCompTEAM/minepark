using System.Threading;

namespace MinePark.Framework.Layers.Base
{
    public abstract class Layer
    {
        protected void WaitCompletion()
        {
            Wait(Defaults.CompletionMsTimeout);
        }

        protected void Wait(int milliseconds)
        {
            Thread.Sleep(milliseconds);
        }
    }
}
