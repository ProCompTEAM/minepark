<?php
namespace minepark\player;

use minepark\player\implementations\MineParkPlayer;

use minepark\Core;
use minepark\Sounds;

class Chatter
{
	public const GLOBAL_CHAT_SIGNATURE = '!';
	public const ADMINISTRATION_CHAT_SIGNATURE = '@';

	private $chatUserPrefixes;

	public function __construct()
	{
		$this->chatUserPrefixes = ["{ChatUserPrefix1}", "{ChatUserPrefix2}", "{ChatUserPrefix3}",
			"{ChatUserPrefix4}", "{ChatUserPrefix5}", "{ChatUserPrefix6}", "{ChatUserPrefix7}",
			"{ChatUserPrefix8}", "{ChatUserPrefix9}", "{ChatUserPrefix10}", "{ChatUserPrefix11}"];
	}

	public function getCore() : Core
	{
		return Core::getActive();
	}

	public function sendGlobal(MineParkPlayer $sender, string $message) 
	{
		if($sender->getStatesMap()->isBeginner) {
			$sender->sendMessage("§eЭтот чат станет доступен после того, как вы отыграете некоторое время.");
			return;
		}

		if(!$this->getCore()->getPhone()->hasStream($sender)) {
			$sender->sendMessage("§cНет телефонной вышки поблизости, чтобы отправить сообщение!");
			return;
		}

		foreach($this->getCore()->getServer()->getOnlinePlayers() as $target) {
			$this->sendGlobalMessage($target, $sender->getProfile()->fullName, $message);
		}
	}

	public function sendForAdministration(MineParkPlayer $sender, string $message) 
	{
		if(!$sender->isAdministrator()) {
			$sender->sendMessage("§cДоступ к этому чату есть только у администрации проекта!");
			return;
		}
		
		foreach($this->getCore()->getServer()->getOnlinePlayers() as $target) {
			$target = MineParkPlayer::cast($target);
			if($target->isAdministrator()) {
				$this->sendMessageForAdministrator($target, $sender->getProfile()->fullName, $message);
			}
		}
	}
	
	public function send(MineParkPlayer $sender, string $message, string $prefix = " §8сказал(а) §7>", int $rad = 7)
	{
		$senderName = strtolower($sender->getName());
		$makeFriends = false;

		$dictionary = ["мое имя", "меня зовут", "звать меня", "my name","мене звати", "mam na imię",
			"mano vardas", "ich heiße", "mon nom est", "Менің атым", "mi chiamo"]; //utf8
		
		array_push($dictionary, explode(' ', $senderName)[0]);
		array_push($dictionary, strtolower($senderName));

		foreach($dictionary as $wd) { 
			$msg = mb_strtolower($message);

			if(preg_match("/".$wd."/iU", $msg) or preg_match("/".$wd."/i", $msg)) {
				$makeFriends = true;
			}
		}

		foreach($this->getCore()->getApi()->getRegionPlayers($sender, $rad) as $target) {
			$this->sendMessage($target, $senderName, $makeFriends, $message, $prefix);
		}
	}

	private function sendGlobalMessage(MineParkPlayer $target, string $senderName, string $message) 
	{
		if($this->getCore()->getPhone()->hasStream($target)) {
			$target->sendMessage("§7<§eСмартфон §d: §8Сообщество§7> §fпользователь §3" . $senderName . " §fпишет§c: §7" . substr($message, 1));
			$target->sendSound(Sounds::CHAT_SOUND);
		}
	}

	private function sendMessageForAdministrator(MineParkPlayer $target, string $senderName, string $message) 
	{
		$target->sendMessage("§7<§aАдминистрация§7> §3" . $senderName . " §fпишет§c: §7" . substr($message, 1));
		$target->sendSound(Sounds::CHAT_SOUND);
	}

	private function sendMessage(MineParkPlayer $target, string $senderName, bool $makeFriends, string $message, string $prefix) 
	{
		$nickname = null;

		if(strpos($target->getProfile()->people, $senderName) !== false) {
			$nickname = "§7" . $senderName;
		} elseif($makeFriends and $senderName != strtolower($target->getName())) {
			$target->getProfile()->people .= $senderName;
			$this->getCore()->getProfiler()->saveProfile($target);
			$nickname = "§2" . $senderName;
		} elseif($senderName == strtolower($target->getName())) {
			$nickname = "{ChatIAm}";
		} else {
			$nickname = "§7" . $this->getRandomChatPrefix();
		}

		$target->sendLocalizedMessage($nickname . $prefix. " ". $message);
	}

	private function getRandomChatPrefix() : string
	{
		return $this->chatUserPrefixes[rand(0, count($this->chatUserPrefixes) - 1)];
	}
}
?>