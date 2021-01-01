using Flare_Sharp.Memory;
using Flare_Sharp.Memory.FlameSDK;
using MinePark.Framework.Layers.Base;

namespace MinePark.Framework.Layers
{
    public class GameLayer : Layer
    {
        public bool IsConnectionClosed()
        {
            return true;
        }

        public string GetPlayerName()
        {
            var entity = new PlayerEntity(Minecraft.clientInstance.localPlayer.addr);
            return entity.username;
        }
    }
}
