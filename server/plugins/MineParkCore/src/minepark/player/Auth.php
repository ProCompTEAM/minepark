<?php
namespace minepark\player;

use minepark\Api;
use minepark\Core;
use pocketmine\Player;

use minepark\utils\CallbackTask;
use minepark\command\JailExitCommand;
use minepark\mdc\dto\PasswordDto;
use minepark\mdc\sources\UsersSource;

class Auth
{
	public const STATE_REGISTER = 0;
	public const STATE_NEED_AUTH = 1;
	public const STATE_AUTO = 2;

	public const WELCOME_MESSAGE_TIMEOUT = 2;

	private $ips = [];

	public function getCore() : Core
	{
		return Core::getActive();
	}
	
	public function checkState(Player $player) : int
	{
		if(!$this->getRemoteSource()->isUserPasswordExist($player->getName())) {
			return self::STATE_REGISTER;
		} else {
			if(isset($this->ips[$player->getName()]) and $this->ips[$player->getName()] == $player->getAddress()) {
				return self::STATE_AUTO;
			}
			else {
				return self::STATE_NEED_AUTH;
			}
		}
	}
	
	public function preLogin(Player $player)
	{
		$state = $this->checkState($player);

		$this->setMovement($player, false);

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
			} elseif(md5($password) == $this->getRemoteSource()->getUserPassword($player->getName())) {
				$this->logInUser($player);
			} else {
				$player->kick("AuthInvalid");
			}
		}
	}
	
	public function setMovement($player, bool $status)
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
		$player->auth = true;
		$player->bar = null;

		$this->ips[$player->getName()] = $player->getAddress();
		
		if($this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_ARRESTED)) {
			$this->getCore()->getMapper()->teleportPoint($player, JailExitCommand::JAIL_POINT_NAME);
		} else {
			$this->sendWelcomeText($player);
		}
		
		$this->setMovement($player, true);
	}

	private function registerUser(Player $player, string $password)
	{
		$this->updatePassword($player, $password);
		$this->ips[$player->getName()] = $player->getAddress();

		$player->auth = true;
		$player->bar = null; 

		$this->sendWelcomeText($player);
		$player->sendLocalizedMessage("{AuthStart}" . $password);

		$this->setMovement($player, true);
	}

	private function updatePassword(Player $player, string $password) 
	{
		$passwordDto = new PasswordDto();
		$passwordDto->name = $player->getName();
		$passwordDto->password = md5($password);

		$this->getRemoteSource()->setUserPassword($passwordDto);
	}

	private function autoLogInUser(Player $player)
	{
		$player->auth = true; 
		$player->bar = null;

		$this->setMovement($player, true);

		$this->getCore()->getScheduler()->scheduleDelayedTask(
			new CallbackTask(array($this, "sendWelcomeText"), array($player)), 20 * self::WELCOME_MESSAGE_TIMEOUT);
	}

	private function getRemoteSource() : UsersSource
	{
		return $this->getCore()->getMDC()->getSource(UsersSource::ROUTE);
	}
}
?>