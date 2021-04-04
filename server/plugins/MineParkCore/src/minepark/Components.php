<?php
namespace minepark;

use Exception;
use minepark\components\Auth;
use minepark\components\base\Component;
use minepark\components\BossBar;
use minepark\components\Broadcasting;
use minepark\components\Damager;
use minepark\components\FastFood;
use minepark\components\GameChat;
use minepark\components\GPS;
use minepark\components\NotifyPlayers;
use minepark\components\organisations\Organisations;
use minepark\components\PayDay;
use minepark\components\Phone;
use minepark\components\settings\PlayerSettings;
use minepark\components\Reporting;
use minepark\components\settings\WorldSettings;
use minepark\components\StatusBar;
use minepark\components\Tracking;
use minepark\components\Vehicles;
use minepark\components\TrafficLights;
use minepark\components\WorldProtector;
use minepark\defaults\ComponentAttributes;

class Components
{
    private static array $components;

    public static function initializeAll()
    {
        self::$components = [
            new Organisations,
            new Auth,
            new BossBar,
            new Broadcasting,
            new Damager,
            new FastFood,
            new GameChat,
            new GPS,
            new NotifyPlayers,
            new PayDay,
            new Phone,
            new PlayerSettings,
            new WorldSettings,
            new Reporting,
            new StatusBar,
            new Tracking,
            new Vehicles,
            new TrafficLights,
            new WorldProtector
        ];

        foreach(self::$components as $component) {
            $component->initialize();
        }
    }

    public static function getComponent(string $componentName) : ?Component
    {
        foreach (self::$components as $component) {
            if($componentName === $component::class) {
                if(!$component->hasAttribute(ComponentAttributes::SHARED)) {
                    throw new Exception("Component is not shareable");
                }

                return $component;
            }
        }

        return null;
    }
}
?>