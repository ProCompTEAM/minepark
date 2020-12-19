using Microsoft.VisualStudio.TestTools.UnitTesting;
using MinePark.Framework;
using MinePark.Framework.Layers;
using TechTalk.SpecFlow;

namespace MinePark.AutoTests.Steps.Login
{
    [Binding]
    public class CommonSteps
    {
        private CommonLayer commonLayer = Store.GetLayer<CommonLayer>();

        [Given(@"Открытое меню с серверами")]
        public void ServersMenuOpened()
        {
            bool isMinecraftOpened = commonLayer.IsMinecraftOpened();
            Assert.IsTrue(isMinecraftOpened, "Процесс с игрой не найден");

            commonLayer.ActivateMinecraftWindow();
        }
    }
}
