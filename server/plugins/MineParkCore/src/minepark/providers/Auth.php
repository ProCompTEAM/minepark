<?php
namespace minepark\player;

use minepark\Api;
use minepark\Core;
use minepark\common\player\MineParkPlayer;

use minepark\utils\CallbackTask;
use minepark\Mapper;
use minepark\models\dtos\PasswordDto;
use minepark\providers\data\UsersSource;

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
	
	public function checkState(MineParkPlayer $player) : int
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
	
	public function preLogin(MineParkPlayer $player)
	{
		$state = $this->checkState($player);

		$this->setMovement($player, false);

		switch($state) {
			case self::STATE_REGISTER:
				$player->getStatesMap()->bar = "AuthPasswordRegister"; 
			break;
			case self::STATE_NEED_AUTH:
				$player->getStatesMap()->bar = "AuthPasswordLogin"; 
			break;
			case self::STATE_AUTO:
				$this->autoLogInUser($player);
			break;
			default:
				$player->getStatesMap()->bar = "AuthError"; 
			break;
		}
	}
	
	public function login(MineParkPlayer $player, string $password)
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

	public function sendWelcomeText(MineParkPlayer $player)
	{	
		$player->addTitle("WelcomeTitle1","WelcomeTitle2", 5);
		$this->sendWelcomeChatText($player);
	}
	
	public function sendWelcomeChatText(MineParkPlayer $player)
	{
		$player->sendMessage("WelcomeTextMessage1");
		$player->sendMessage("WelcomeTextMessage2");
		$player->sendMessage("WelcomeTextMessage3");
		$player->sendMessage("WelcomeTextMessage4");
		$player->sendMessage("WelcomeTextMessage5");
	}

	private function logInUser(MineParkPlayer $player)
	{
		$player->getStatesMap()->auth = true;
		$player->getStatesMap()->bar = null;

		$this->ips[$player->getName()] = $player->getAddress();
		
		if($this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_ARRESTED)) {
			$this->getCore()->getMapper()->teleportPoint($player, Mapper::POINT_NAME_JAIL);
		} else {
			$this->sendWelcomeText($player);
		}
		
		$this->setMovement($player, true);
	}

	private function registerUser(MineParkPlayer $player, string $password)
	{
		$this->updatePassword($player, $password);
		$this->ips[$player->getName()] = $player->getAddress();

		$player->getStatesMap()->auth = true;
		$player->getStatesMap()->bar = null; 

		$this->sendWelcomeText($player);
		$player->sendLocalizedMessage("{AuthStart}" . $password);

		$this->setMovement($player, true);
	}

	private function updatePassword(MineParkPlayer $player, string $password) 
	{
		$passwordDto = new PasswordDto();
		$passwordDto->name = $player->getName();
		$passwordDto->password = md5($password);

		$this->getRemoteSource()->setUserPassword($passwordDto);
	}

	private function autoLogInUser(MineParkPlayer $player)
	{
		$player->getStatesMap()->auth = true; 
		$player->getStatesMap()->bar = null;

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