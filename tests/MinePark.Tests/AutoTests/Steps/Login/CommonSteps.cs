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

        private KeyboardLayer keyboardLayer = Store.GetLayer<KeyboardLayer>();

        [Given(@"Игра запущена, представлен интерфейс главного меню игры")]
        public void GameStarted()
        {
            bool isMinecraftOpened = commonLayer.IsMinecraftOpened();
            Assert.IsTrue(isMinecraftOpened, "Процесс с игрой не найден");

            commonLayer.ActivateGameWindow();
        }

        [When(@"Я перехожу в меню выбора серверов")]
        public void OpenServerMenu()
        {
            keyboardLayer.SendKeyUp(4);

            keyboardLayer.Enter();
        }

        [When(@"Я ожидаю секунду, пока данные загрузятся")]
        public void WaitTheSecond()
        {
            commonLayer.WaitMilliseconds(1000);
        }

        [When(@"Я ожидаю две секунды, пока данные загрузятся")]
        public void Wait2Seconds()
        {
            commonLayer.WaitMilliseconds(2000);
        }

        [When(@"Я ожидаю три секунды, пока данные загрузятся")]
        public void Wait3Seconds()
        {
            commonLayer.WaitMilliseconds(3000);
        }

        [When(@"Я открываю вкладку с серверами")]
        public void OpenServerTab()
        {
            keyboardLayer.SendRightSquareBracket(2);
        }

        [When(@"Я выбираю сервер в списке серверов")]
        public void SelectServer()
        {
            keyboardLayer.SendKeyDown(2);

            keyboardLayer.Tab();

            keyboardLayer.Enter();
        }
    }
}
