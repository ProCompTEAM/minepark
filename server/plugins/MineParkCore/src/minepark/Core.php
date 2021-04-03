<?php
namespace minepark;

use minepark\Api;
use minepark\Providers;
use minepark\common\MDC;
use minepark\Events;
use minepark\defaults\Files;
use minepark\external\WebApi;
use pocketmine\event\Listener;
use jojoe77777\FormAPI\FormAPI;
use minepark\defaults\Defaults;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use minepark\external\service\Service;
use pocketmine\command\ConsoleCommandSender;

class Core extends PluginBase implements Listener
{
    private static Core $_instance;

    private Events $events;
    private MDC $mdc;
    private Api $sapi;
    private Commands $commands;
    private Service $service;
    private WebApi  $webapi;

    public static function getActive() : Core
    {
        return self::$_instance;
    }

    public function onEnable()
    {
        Core::$_instance = $this;

        $this->applyCommonSettings();
        $this->applyServerSettings();

        $this->initializeDefaultDirectories();

        $this->initializeEvents();
        $this->initializeTasks();
        $this->initializeProviders();
        $this->initializeMDC();
        $this->initializeCommonModules();
    }

    public function onDisable()
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

    public function initializeCommonModules()
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
        return $this->events;
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
        $cmd = $commandMap->getCommand($commandName);
        $cmd->unregister($commandMap);
        $commandMap->unregister($cmd);
    }

    private function transferPlayersToLobby()
    {
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player->transfer(Defaults::SERVER_LOBBY_ADDRESS, Defaults::SERVER_LOBBY_PORT);
        }
    }
}
?>