<?php
namespace minepark;

use minepark\Api;
use minepark\Providers;
use minepark\common\MDC;
use minepark\components\GPS;
use minepark\components\Auth;
use minepark\Events;
use minepark\components\Phone;
use minepark\defaults\Files;
use minepark\components\PayDay;
use minepark\external\WebApi;
use minepark\components\Damager;
use minepark\components\Tracker;
use minepark\components\FastFood;
use minepark\components\GameChat;
use minepark\components\Reporter;
use pocketmine\event\Listener;
use jojoe77777\FormAPI\FormAPI;
use minepark\components\BossBar;
use minepark\components\Broadcaster;
use minepark\defaults\Defaults;
use minepark\components\StatusBar;
use pocketmine\command\Command;
use minepark\components\settings\PlayerSettings;
use pocketmine\plugin\PluginBase;
use minepark\components\NotifyPlayers;
use minepark\components\WorldProtector;
use pocketmine\command\CommandSender;
use minepark\external\service\Service;
use pocketmine\command\ConsoleCommandSender;
use minepark\components\organisations\Organisations;
use minepark\components\VehicleManager;

class Core extends PluginBase implements Listener
{
    private static Core $_instance;

    private $eventsHandler;

    private $mdc;

    private $sapi;
    private $commands;
    private $organisations;
    private $service;
    private $chatter;
    private $initializer;
    private $damager;
    private $reporter;
    private $protector;
    private $phone;
    private $statusbar;
    private $auth;
    private $notifier;
    private $gpsmod;
    private $fastfood;
    private $tracker;
    private $broadcaster;
    private $vehicleManager;
    private $bossBar;

    public $webapi;

    public static function getActive() : Core
    {
        return self::$_instance;
    }

    public function onEnable()
    {
        Core::$_instance = $this;

        $this->initializeEvents();

        Tasks::initializeAll();

        Providers::initializeAll();

        $this->initializeMDC();

        $this->initializeComponents();

        $this->initializeDefaultDirectories();

        $this->applyServerSettings();
    }
    
    public function onDisable()
    {
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player->transfer(Defaults::SERVER_LOBBY_ADDRESS, Defaults::SERVER_LOBBY_PORT);
        }
    }

    public function initializeMDC()
    {
        $this->mdc = new MDC;
        $this->getMDC()->initializeAll();
    }

    public function initializeEvents()
    {
        Events::initializeAll();
        $this->eventsHandler = new Events;
        $this->getServer()->getPluginManager()->registerEvents($this->eventsHandler, $this);
    }

    public function initializeComponents()
    {
        $this->sapi = new Api;
        $this->scmd = new Commands;
        $this->service = new Service;
        $this->webapi = new WebApi;

        Components::initializeAll();
    }

    public function getTargetDirectory(bool $strings = false) : string
    {
        return $strings ? Files::DEFAULT_DIRECTORY_STRINGS : Files::DEFAULT_DIRECTORY;
    }

    public function getMDC() : MDC
    {
        return $this->mdc;
    }

    public function getEvents() : Events
    {
        return $this->eventsHandler;
    }
    
    public function getApi() : Api
    {
        return $this->sapi;
    }

    public function getWebApi() : WebApi
    {
        return $this->webapi;
    }

    public function getService() : Service
    {
        return $this->service;
    }
    
    public function getReporter() : Reporter
    {
        return $this->reporter;
    }
    
    public function getCommands() : Commands
    {
        return $this->commands;
    }

    public function getOrganisationsModule() : Organisations
    {
        return $this->organisations;
    }
    
    public function getChatter() : GameChat
    {
        return $this->chatter;
    }

    public function getPlayerSettings() : PlayerSettings
    {
        return $this->initializer;
    }

    public function getDamager() : Damager
    {
        return $this->damager;
    }

    public function getWorldProtector() : WorldProtector
    {
        return $this->protector;
    }

    public function getPhone() : Phone
    {
        return $this->phone;
    }

    public function getNavigator() : GPS
    {
        return $this->gpsmod;
    }
    
    public function getStatusBar() : StatusBar
    {
        return $this->statusbar;
    }

    public function getAuthModule() : Auth
    {
        return $this->auth;
    }

    public function getFoodModule() : FastFood
    {
        return $this->fastfood;
    }

    public function getPayDayModule() : PayDay
    {
        return $this->payday;
    }

    public function getTrackerModule() : Tracker
    {
        return $this->tracker;
    }

    public function getBroadcasterModule() : Broadcaster
    {
        return $this->broadcaster;
    }

    public function getNotifierModule() : NotifyPlayers
    {
        return $this->notifier;
    }

    public function getVehicleManager() : VehicleManager
    {
        return $this->vehicleManager;
    }

    public function getBossBarModule() : BossBar
    {
        return $this->bossBar;
    }

    public function getFormApi() : FormAPI
    {
        return $this->getServer()->getPluginManager()->getPlugin("FormAPI");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        if ($command->getName() === Service::COMMAND and $sender instanceof ConsoleCommandSender) {
            $this->getService()->handle($args);
            return true;
        }

        return false;
    }

    public function sendToMessagesLog(string $prefix, string $message)
    {
        file_put_contents(Files::MESSAGES_LOG_FILE, (PHP_EOL . "(" . $prefix . ") - " . $message), FILE_APPEND);
    }

    private function initializeDefaultDirectories()
    {
        if(!file_exists(Files::DEFAULT_DIRECTORY)) {
            mkdir(Files::DEFAULT_DIRECTORY);
        }

        if(!file_exists(Files::DEFAULT_DIRECTORY_STRINGS)) {
            mkdir(Files::DEFAULT_DIRECTORY_STRINGS);
        }
    }

    private function applyServerSettings()
    {
        $this->getApi()->removeDefaultServerCommand("say");
        $this->getApi()->removeDefaultServerCommand("defaultgamemode");
        $this->getApi()->removeDefaultServerCommand("version");
        $this->getApi()->removeDefaultServerCommand("difficulty");
        $this->getApi()->removeDefaultServerCommand("tell");
        $this->getApi()->removeDefaultServerCommand("kill");
    }
}
?>