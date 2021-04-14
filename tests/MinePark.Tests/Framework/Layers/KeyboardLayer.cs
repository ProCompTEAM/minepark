using MinePark.Framework.Api;
using MinePark.Framework.Layers.Base;
using System;
using WindowsInput;
using WindowsInput.Native;

namespace MinePark.Framework.Layers
{
    public class KeyboardLayer : Layer
    {
        private const int DefaultKeyCount = 1;

        private InputSimulator inputSimulator = new InputSimulator();

        public void SendKeyUp(int count = DefaultKeyCount) => KeyPress(VirtualKeyCode.UP, count);

        public void SendKeyDown(int count = DefaultKeyCount) => KeyPress(VirtualKeyCode.DOWN, count);

        public void SendKeyLeft(int count = DefaultKeyCount) => KeyPress(VirtualKeyCode.LEFT, count);

        public void SendKeyRight(int count = DefaultKeyCount) => KeyPress(VirtualKeyCode.RIGHT, count);

        public void SendLeftSquareBracket(int count = DefaultKeyCount) => KeyPress(VirtualKeyCode.OEM_4, count);

        public void SendRightSquareBracket(int count = DefaultKeyCount) => KeyPress(VirtualKeyCode.OEM_6, count);

        public void Enter() => KeyPress(VirtualKeyCode.RETURN);

        public void Tab() => KeyPress(VirtualKeyCode.TAB);

        public void Escape() => KeyPress(VirtualKeyCode.ESCAPE);

        public void OpenChat() => KeyPress(VirtualKeyCode.VK_T);

        public void TakeScreenshot()
        {
            inputSimulator.Keyboard.KeyPress(VirtualKeyCode.CONTROL);

            inputSimulator.Keyboard.KeyPress(VirtualKeyCode.SNAPSHOT);

            WaitCompletion();
        }

        private void KeyPress(VirtualKeyCode virtualKeyCode, int count = DefaultKeyCount)
        {
            for (int loops = 0; loops < count; loops++)
            {
                inputSimulator.Keyboard.KeyPress(virtualKeyCode);

                WaitCompletion();
            }
        }
    }
}
