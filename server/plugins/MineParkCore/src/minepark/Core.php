<?php
namespace minepark;

use minepark\Events;
use minepark\Providers;
use minepark\common\MDC;
use minepark\defaults\Files;
use pocketmine\event\Listener;
use pocketmine\world\Position;
use jojoe77777\FormAPI\FormAPI;
use minepark\defaults\Defaults;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use minepark\defaults\Permissions;
use pocketmine\command\CommandSender;
use minepark\external\service\Service;
use pocketmine\command\ConsoleCommandSender;

class Core extends PluginBase implements Listener
{
    private static Core $_instance;

    private Events $events;
    private MDC $mdc;
    private Commands $commands;
    private Service $service;

    public static function getActive() : Core
    {
        return self::$_instance;
    }

    public function onEnable(): void
    {
        Core::$_instance = $this;

        $this->applyCommonSettings();
        $this->applyServerSettings();

        $this->initializeDefaultDirectories();

        $this->initializeEvents();
        $this->initializeTasks();
        $this->initializeProviders();
        $this->initializeMDC();
        $this->initializeComponents();
        $this->initializeCommands();
        $this->initializeServiceModule();
    }

    public function onDisable(): void
    {
        $this->transferPlayersToLobby();
    }

    public function initializeEvents()
    {
        Events::initializeAll();
        $this->events = new Events;
        $this->getServer()->getPluginManager()->registerEvents($this->events, $this);
    }

    public function initializeTasks()
    {
        Tasks::initializeAll();
    }

    public function initializeProviders()
    {
        Providers::initializeAll();
    }

    public function initializeMDC()
    {
        $this->mdc = new MDC;
        $this->getMDC()->initializeAll();
    }

    public function initializeComponents()
    {
        Components::initializeAll();
    }

    public function initializeCommands()
    {
        $this->scmd = new Commands;
    }

    public function initializeServiceModule()
    {
        $this->service = new Service;
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
        return $this->events;
    }

    public function getService() : Service
    {
        return $this->service;
    }
    
    public function getCommands() : Commands
    {
        return $this->commands;
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

    public function getAdministration(bool $namesOnly = false) : array
    {
        $list = [];

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            if ($player->hasPermission(Permissions::ADMINISTRATOR) or $player->getServer()->isOp($player)) {
                $namesOnly ? array_push($list, $player->getName()) : array_push($list, $player);
            }
        }

        return $list;
    }

    public function getRegionPlayers(Position $position, int $distance) : array
    {
        //TODO: Replace to eye-vector logic
        $players = array();

        foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            if($onlinePlayer->distance($position) < $distance) {
                array_push($players, $onlinePlayer);
            }
        }

        return $players;
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

    private function applyCommonSettings()
    {
        ini_set("date.timezone", "Europe/Kiev");
    }

    private function applyServerSettings()
    {
        $this->removeDefaultServerCommand("say");
        $this->removeDefaultServerCommand("defaultgamemode");
        $this->removeDefaultServerCommand("version");
        $this->removeDefaultServerCommand("difficulty");
        $this->removeDefaultServerCommand("tell");
        $this->removeDefaultServerCommand("kill");
    }

    private function removeDefaultServerCommand(string $commandName)
    {
        $commandMap = $this->getServer()->getCommandMap();
        $command = $commandMap->getCommand($commandName);
        $command->unregister($commandMap);
        $commandMap->unregister($command);
    }

    private function transferPlayersToLobby()
    {
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player->transfer(Defaults::SERVER_LOBBY_ADDRESS, Defaults::SERVER_LOBBY_PORT);
        }
    }
}