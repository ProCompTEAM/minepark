<?php
namespace minepark;

use minepark\Api;
use minepark\mdc\MDC;
use minepark\modules\GPS;
use minepark\player\Auth;
use minepark\player\Bank;
use minepark\EventsHandler;

use minepark\modules\Phone;
use minepark\modules\PayDay;
use minepark\player\Chatter;
use minepark\player\Damager;
use pocketmine\utils\Config;
use minepark\CommandsHandler;
use minepark\external\WebApi;
use minepark\modules\Tracker;
use minepark\modules\FastFood;
use minepark\modules\Reporter;
use minepark\player\Localizer;
use pocketmine\event\Listener;
use jojoe77777\FormAPI\FormAPI;
use minepark\modules\StatusBar;
use pocketmine\command\Command;
use minepark\player\Initializer;
use pocketmine\plugin\PluginBase;
use minepark\modules\NotifyPlayers;
use pocketmine\command\CommandSender;
use minepark\external\service\Service;
use minepark\mdc\dto\UserDto;
use pocketmine\command\ConsoleCommandSender;
use minepark\modules\organisations\Organisations;

class Core extends PluginBase implements Listener
{
	public const SERVER_LOBBY_ADDRESS = "minepark.ru";
	public const SERVER_LOBBY_PORT = 19132;

	public const DEFAULT_DIRECTORY = "data/";
	public const DEFAULT_DIRECTORY_STRINGS = "data/strings/";

	public const MESSAGES_LOG_FILE = "msg-log.txt";
	public const WEBAPI_LOG_FILE = "webapi-log.txt";

	static private $_core;

	private $eventsHandler;

	private $mdc;

	private $sapi;
	private $scmd;
	private $organisations;
	private $service;
	private $bank;
	private $profiler;
	private $mapper;
	private $chatter;
	private $initializer;
	private $localizer;
	private $damager;
	private $reporter;
	private $phone;
	private $statusbar;
	private $auth;
	private $notifier;
	private $gpsmod;
	private $fastfood;
	private $tracker;

	public $webapi;

	public static function getActive() : Core
	{
		return self::$_core;
	}
	
    public function onEnable()
	{
		Core::$_core = $this;

		$this->initializeMDC();

		$this->initializeEventsHandler();

		$this->initialize();

		if(!file_exists(self::DEFAULT_DIRECTORY)) {
			mkdir(self::DEFAULT_DIRECTORY);
		}

		if(!file_exists(self::DEFAULT_DIRECTORY_STRINGS)) {
			mkdir(self::DEFAULT_DIRECTORY_STRINGS);
		}
    }
	
	public function onDisable()
	{
		foreach($this->getServer()->getOnlinePlayers() as $player) {
			$player->transfer(self::SERVER_LOBBY_ADDRESS, self::SERVER_LOBBY_PORT);
		}
	}

	public function initializeMDC()
	{
		$this->mdc = new MDC;
		$this->getMDC()->initializeAll();
	}

	public function initializeEventsHandler()
	{
		$this->eventsHandler = new EventsHandler;
		$this->getServer()->getPluginManager()->registerEvents($this->eventsHandler, $this);
	}

	public function initialize()
	{
		$this->sapi = new Api;
		$this->scmd = new CommandsHandler;
		$this->organisations = new Organisations;
		$this->service = new Service;
		$this->bank = new Bank;
		$this->profiler = new Profiler;
		$this->mapper = new Mapper;
		$this->chatter = new Chatter;
		$this->initializer = new Initializer;
		$this->localizer = new Localizer;
		$this->damager = new Damager;
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
	}

	public function getTargetDirectory(bool $strings = false) : string
	{
		return $strings ? self::DEFAULT_DIRECTORY_STRINGS : self::DEFAULT_DIRECTORY;
	}

	public function getMDC() : MDC
	{
		return $this->mdc;
	}

	public function getEventsHandler() : EventsHandler
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

	public function getBank() : Bank
	{
		return $this->bank;
	}

	public function getProfiler() : Profiler
	{
		return $this->profiler;
	}

	public function getMapper() : Mapper
	{
		return $this->mapper;
	}
	
	public function getChatter() : Chatter
	{
		return $this->chatter;
	}

	public function getInitializer() : Initializer
	{
		return $this->initializer;
	}

	public function getLocalizer() : Localizer
	{
		return $this->localizer;
	}

	public function getDamager() : Damager
	{
		return $this->damager;
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

	public function getNotifierModule() : NotifyPlayers
	{
		return $this->notifier;
	}

	public function getFormApi() : FormAPI
	{
		return $this->getServer()->getPluginManager()->getPlugin("FormAPI");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
	{
		if($command == Service::COMMAND && $sender instanceof ConsoleCommandSender) {
			$this->getService()->handle($args);
			return true;
		}

		return false;
	}
}
?>