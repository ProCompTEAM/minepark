<?php
namespace minepark;

use Exception;
use minepark\components\administrative\BanSystem;
use minepark\components\Auth;
use minepark\components\BossBar;
use minepark\components\map\ATM;
use minepark\components\FastFood;
use minepark\components\chat\Chat;
use minepark\components\StatusBar;
use minepark\components\phone\Phone;
use minepark\components\Broadcasting;
use minepark\components\map\ClearLagg;
use minepark\components\base\Component;
use minepark\components\chat\ChatAudit;
use minepark\components\map\Navigation;
use minepark\components\WorldProtector;
use minepark\components\map\FloatingTexts;
use minepark\components\map\TrafficLights;
use minepark\components\vehicles\Vehicles;
use minepark\defaults\ComponentAttributes;
use minepark\components\map\PlayersLocation;
use minepark\components\organisations\PayDay;
use minepark\components\administrative\Reports;
use minepark\components\settings\WorldSettings;
use minepark\components\administrative\Tracking;
use minepark\components\settings\EntitySettings;
use minepark\components\settings\PlayerSettings;
use minepark\components\organisations\Organisations;
use minepark\components\administrative\PermissionsSwitch;

class Components
{
    private static array $components;

    public static function initializeAll()
    {
        self::$components = [
            new EntitySettings,
            new PlayerSettings,
            new WorldSettings,
            new Chat,
            new ChatAudit,
            new Organisations,
            new Phone,
            new Auth,
            new BossBar,
            new Broadcasting,
            new FastFood,
            new Navigation,
            new PlayersLocation,
            new PayDay,
            new Reports,
            new StatusBar,
            new Tracking,
            new Vehicles,
            new TrafficLights,
            new WorldProtector,
            new PermissionsSwitch,
            new FloatingTexts,
            new ATM,
            new ClearLagg,
            new BanSystem
        ];

        foreach(self::$components as $component) {
            $component->initialize();
        }
    }

    public static function getComponent(string $componentName) : Component
    {
        foreach (self::$components as $component) {
            if($componentName === $component::class) {
                if(!$component->hasAttribute(ComponentAttributes::SHARED)) {
                    throw new Exception("Component is not shareable");
                }

                return $component;
            }
        }

        throw new Exception("Component '" . $componentName . "' does not exist");
    }
}