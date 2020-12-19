using Microsoft.VisualStudio.TestTools.UnitTesting;
using MinePark.Framework;
using System.Diagnostics;
using System.Linq;
using TechTalk.SpecFlow;

namespace MinePark.AutoTests.Steps.Login
{
    [Binding]
    public class CommonSteps
    {
        [Given(@"Открытое меню с серверами")]
        public void ServerMenuOpened()
        {
            bool isMinecraftOpened = IsMinecraftOpened();
            Assert.IsTrue(isMinecraftOpened, "Процесс с игрой не найден");
        }

        private bool IsMinecraftOpened()
        {
            return Process.GetProcessesByName(Defaults.GameProcessName).Any();
        }
    }
}
