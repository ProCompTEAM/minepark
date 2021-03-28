<?php
namespace minepark;

use minepark\components\Auth;
use minepark\components\BossBar;
use minepark\components\Broadcaster;
use minepark\components\Damager;
use minepark\components\FastFood;
use minepark\components\GameChat;
use minepark\components\GPS;
use minepark\components\NotifyPlayers;
use minepark\components\organisations\Organisations;
use minepark\components\PayDay;
use minepark\components\Phone;
use minepark\components\settings\PlayerSettings;
use minepark\components\Reporter;
use minepark\components\settings\WorldSettings;
use minepark\components\StatusBar;
use minepark\components\Tracker;
use minepark\components\VehicleManager;
use minepark\components\WorldProtector;

class Components
{
    private static array $components;

    public static function initializeAll()
    {
        self::$components = [
            new Organisations,
            new Auth,
            new BossBar,
            new Broadcaster,
            new Damager,
            new FastFood,
            new GameChat,
            new GPS,
            new NotifyPlayers,
            new PayDay,
            new Phone,
            new PlayerSettings,
            new WorldSettings,
            new Reporter,
            new StatusBar,
            new Tracker,
            new VehicleManager,
            new WorldProtector
        ];
    }
}
?>