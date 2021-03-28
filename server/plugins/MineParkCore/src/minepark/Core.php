<?php
namespace minepark;

use minepark\Api;
use minepark\Profiler;
use minepark\Providers;
use minepark\common\MDC;
use minepark\components\GPS;
use minepark\components\Auth;
use minepark\Events;
use minepark\components\Phone;
use minepark\defaults\Files;
use minepark\components\PayDay;
use minepark\CommandsHandler;
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
use minepark\components\PlayerInitialization;
use pocketmine\plugin\PluginBase;
use minepark\components\NotifyPlayers;
use minepark\components\WorldProtector;
use pocketmine\command\CommandSender;
use minepark\external\service\Service;
use pocketmine\command\ConsoleCommandSender;
use minepark\components\organisations\Organisations;
use minepark\components\VehicleManager;
use pocketmine\event\Event;

class Core extends PluginBase implements Listener
{
    private static Core $_instance;

    private $eventsHandler;

    private $mdc;

    private $sapi;
    private $scmd;
    private $organisations;
    private $service;
    private $profiler;
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

        Tasks::initializeAll();

        Providers::initializeAll();

        $this->initializeMDC();

        $this->initializeEvents();

        $this->initialize();

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

    public function initialize()
    {
        $this->sapi = new Api;
        $this->scmd = new CommandsHandler;
        $this->organisations = new Organisations;
        $this->service = new Service;
        $this->profiler = new Profiler;
        $this->chatter = new GameChat;
        $this->initializer = new PlayerInitialization;
        $this->damager = new Damager;
        $this->protector = new WorldProtector;
        $this->phone = new Phone;
        $this->statusbar = new StatusBar;
        $this->auth = new Auth;
        $this->payday = new PayDay;
        $this->notifier = new NotifyPlayers;
        $this->gpsmod = new GPS;
        $this->fastfood = new FastFood;
        $this->reporter = new Reporter;
        $this->webapi = new WebApi;
        $this->tracker = new Tracker;
        $this->broadcaster = new Broadcaster;
        $this->vehicleManager = new VehicleManager;
        $this->bossBar = new BossBar;
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
    
    public function getCommandsHandler() : CommandsHandler
    {
        return $this->scmd;
    }

    public function getOrganisationsModule() : Organisations
    {
        return $this->organisations;
    }

    public function getProfiler() : Profiler
    {
        return $this->profiler;
    }
    
    public function getChatter() : GameChat
    {
        return $this->chatter;
    }

    public function getPlayerInitialization() : PlayerInitialization
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