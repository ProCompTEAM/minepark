<?php
namespace minepark\player;

use pocketmine\Player;

use minepark\Core;
use minepark\Sounds;
use minepark\Permission;

class Chatter
{
	public const GLOBAL_CHAT_SIGNATURE = '!';

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

	public function sendGlobal(Player $sender, string $message) 
	{
		if(!$sender->isOp() and !$sender->hasPermission(Permission::CUSTOM)) {
			$sender->sendWindowMessage("§eЧтобы получить доступ к этому чату, необходимо купить подоходящую карту. Сайт: §ahttp://minepark.ru\n§dПриятной игры! :)");
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
	
	public function send(Player $sender, string $message, string $prefix = " §8сказал(а) §7>", int $rad = 7)
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

		foreach($this->getCore()->getApi()->getRegionPlayers($sender->getX(), $sender->getY(), $sender->getZ(), $rad) as $target) {
			$this->sendMessage($target, $senderName, $makeFriends, $message, $prefix);
		}
	}

	private function sendGlobalMessage(Player $target, string $senderName, string $message) 
	{
		if($this->getCore()->getPhone()->hasStream($target)) {
			$target->sendMessage("§7<§eСмартфон §d: §8Card Community§7> §fпользователь §3" . $senderName . " §fпишет§c: §7" . substr($message, 1));
			$target->sendSound(Sounds::CHAT_SOUND);
		}
	}

	private function sendMessage(Player $target, string $senderName, bool $makeFriends, string $message, string $prefix) 
	{
		$nickname = null;

		if(strpos($target->getProfile()->people, $senderName) !== false) {
			$nickname = "§7" . $senderName;
		} elseif($makeFriends and $senderName != strtolower($target->getName())) {
			$target->getProfile()->people .= $senderName;
			$this->getCore()->getInitializer()->updatePlayerSaves($target);
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