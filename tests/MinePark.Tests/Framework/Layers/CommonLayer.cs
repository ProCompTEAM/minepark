using MinePark.Framework.Api;
using MinePark.Framework.Layers.Base;
using System;
using System.Diagnostics;
using System.Linq;

namespace MinePark.Framework.Layers
{
    public class CommonLayer : Layer
    {
        public void WaitMilliseconds(int milliseconds)
        {
            Wait(milliseconds);
        }

        public bool IsGameOpened()
        {
            return GetActiveGameProcess() != null;
        }

        public void ActivateGameWindow()
        {
            IntPtr hwnd = WindowsApi.FindWindow(null, Defaults.GameTitle);
            
            if (WindowsApi.IsIconic(hwnd))
            {
                WindowsApi.ShowWindow(hwnd, 9); //9 - restore
            }
            else
            {
                WindowsApi.SetForegroundWindow(hwnd);
            }
        }

        private Process GetActiveGameProcess()
        {
            return Process.GetProcessesByName(Defaults.GameProcessName).SingleOrDefault();
        }
    }
}
