using Microsoft.VisualStudio.TestTools.UnitTesting;
using MinePark.Framework;
using MinePark.Framework.Layers;
using TechTalk.SpecFlow;

namespace MinePark.AutoTests.Steps.Gameplay
{
    [Binding]
    public class CommonSteps
    {
        private CommonLayer commonLayer = Store.GetLayer<CommonLayer>();

        private KeyboardLayer keyboardLayer = Store.GetLayer<KeyboardLayer>();

        private GameLayer gameLayer = Store.GetLayer<GameLayer>();

        [Given(@"Игра запущена, игрок подключен к серверу")]
        public void GameStartedAndPlayerConnected()
        {
            bool isMinecraftOpened = commonLayer.IsGameOpened();
            Assert.IsTrue(isMinecraftOpened, "Процесс с игрой не найден");

            commonLayer.ActivateGameWindow();

            ResumeGame();
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

        [When(@"Я открываю чат и ввожу '(.*)'")]
        public void OpenAndSendChatMessage(string chatMessage)
        {
            keyboardLayer.OpenChat();

            keyboardLayer.SendMessage(chatMessage);

            keyboardLayer.Enter();
        }

        [Then(@"Сервер закрыл соединение")]
        public void CheckServerClosedConnection()
        {
            var state = gameLayer.IsConnectionClosed();
            Assert.IsTrue(state, "Сервер не закрыл соединение");
        }

        [Then(@"Текущее имя пользователя '(.*)'")]
        public void CheckUsername(string expectedName)
        {
            var currentName = gameLayer.GetPlayerName();
            Assert.AreEqual(expectedName, currentName, $"Имена пользователя не совпадают.");
        }

        private void ResumeGame()
        {
            keyboardLayer.Escape();

            commonLayer.WaitMilliseconds(1000);
        }
    }
}
