<?php
namespace minepark\player;

use minepark\Api;
use minepark\command\JailExitCommand;
use minepark\utils\CallbackTask;
use minepark\Core;

use pocketmine\utils\Config;
use pocketmine\Player;

class Auth
{
	public const STATE_REGISTER = 0;
	public const STATE_NEED_AUTH = 1;
	public const STATE_AUTO = 2;

	public const WELCOME_MESSAGE_TIMEOUT = 2;

	public $config;
	public $dir;
	
	public function __construct()
	{
		$this->dir = $this->getCore()->getTargetDirectory();
		$this->config = new Config($this->dir."logins.json", Config::JSON);
	}
	
	public function checkState(Player $p) : int
	{
		$c = $this->config;
		$name = strtolower($p->getName());

		if(!$c->exists($name)) {
			return self::STATE_REGISTER;
		} else {
			if($c->getNested($name.".ip") == $p->getAddress()) return self::STATE_AUTO;
			else return self::STATE_NEED_AUTH;
		}
	}
	
	public function getCore() : Core
	{
		return Core::getActive();
	}
	
	public function preLogin(Player $player)
	{
		$state = $this->checkState($player);

		$this->setMove($player, false);

		switch($state) {
			case self::STATE_REGISTER:
				$player->bar = "AuthPasswordRegister"; 
			break;
			case self::STATE_NEED_AUTH:
				$player->bar = "AuthPasswordLogin"; 
			break;
			case self::STATE_AUTO:
				$this->autoLogInUser($player);
			break;
			default:
				$player->bar = "AuthError"; 
			break;
		}
	}
	
	public function login(Player $player, string $password)
	{
		$name = strtolower($player->getName());
		$state = $this->checkState($player);

		if($state == self::STATE_REGISTER) {
			if(strlen($password) < 6) {
				$player->sendMessage("AuthLen");
			} else {
				$this->registerUser($player, $password);
			}
		} elseif($state == self::STATE_NEED_AUTH) {
			if(strlen($password) < 6) {
				$player->kick("AuthLen");
			} elseif($password == $this->config->getNested($name.".key")) {
				$this->logInUser($player);
			} else {
				$player->kick("AuthInvalid");
			}
		}
	}
	
	public function setMove($player, bool $status)
	{
		$player->setImmobile(!$status);
	}

	public function sendWelcomeText(Player $player)
	{	
		$player->addTitle("WelcomeTitle1","WelcomeTitle2", 5);
		$this->sendWelcomeChatText($player);
	}
	
	public function sendWelcomeChatText(Player $player)
	{
		$player->sendMessage("WelcomeTextMessage1");
		$player->sendMessage("WelcomeTextMessage2");
		$player->sendMessage("WelcomeTextMessage3");
		$player->sendMessage("WelcomeTextMessage4");
		$player->sendMessage("WelcomeTextMessage5");
	}

	private function logInUser(Player $player)
	{
		$name = strtolower($player->getName());
		$player->auth = true;
		$player->bar = null;

		$this->config->setNested($name.".ip", $player->getAddress());
		$player->save();
		
		if($this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_ARRESTED)) {
			$this->getCore()->getMapper()->teleportPoint($player, JailExitCommand::JAIL_POINT_NAME);
		} else {
			$this->sendWelcomeText($player);
		}
		
		$this->setMove($player, true);
	}

	private function registerUser(Player $player, string $password)
	{
		$name = strtolower($player->getName());
		$c = $this->config;

		$c->setNested($name.".key", $password);
		$c->setNested($name.".ip", $player->getAddress());
		$c->save();

		$player->auth = true;
		$player->bar = null; 

		$this->sendWelcomeText($player);
		$player->sendLocalizedMessage("{AuthStart}" . $password);

		$this->setMove($player, true);
	}

	private function autoLogInUser(Player $player)
	{
		$player->auth = true; 
		$player->bar = null;

		$this->setMove($player, true);

		$this->getCore()->getScheduler()->scheduleDelayedTask(
			new CallbackTask(array($this, "sendWelcomeText"), array($player)), 20 * self::WELCOME_MESSAGE_TIMEOUT);
	}
}
?>