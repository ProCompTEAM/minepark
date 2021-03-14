<?php
namespace minepark;

use minepark\defaults\Files;
use pocketmine\level\Position;
use minepark\defaults\Permissions;
use minepark\common\player\MineParkPlayer;

class Api
{
	public const ATTRIBUTE_HAS_PASSPORT = 'P';
	public const ATTRIBUTE_ARRESTED = 'A';
	public const ATTRIBUTE_WANTED = 'W';
	public const ATTRIBUTE_BOSS = 'B';

	public function getPrefix($pId, $withColor = false) : string
	{
		$form = null;

		switch($pId)
		{
            case 1:  $form = "§0Неизвестный" ; break;
            case 2:  $form = "§5Оператор" ; break;
            case 3:  $form = "§6Заключенный" ; break;
            case 4:  $form = "§2Рабочий" ; break;
            case 5:  $form = "§cДоктор" ; break;
            case 6:  $form = "§aГос. Служащий" ; break;
            case 7:  $form = "§3Служба Охраны" ; break;
            case 8:  $form = "§dПродавец" ; break;
            case 9:  $form = "§eЮрист" ; break;
            case 10: $form = "§4Служба Спасения"; break;

			default: return "§f"; break;
		}

		$withColor ? $form : substr($form, 2);
	}
	
	public function getRegionPlayers(Position $position, int $distance) : array
	{
		$players = array();

		foreach($this->getCore()->getServer()->getOnlinePlayers() as $onlinePlayer) {
			if($onlinePlayer->distance($position) < $distance) {
				array_push($players, $onlinePlayer);
			}
		}

		return $players;
	}
	
	public function getFromArray(array $array, int $min, string $split_str = " ") : string
	{
	   $val = -1;  
	   $str = "";

	   foreach($array as $a) {
			$val = $val + 1;

			if($val > $min) {
				$str .= $split_str.$a;
			} elseif($val == $min) {
				$str .= $a;
			}
	   }

	   return $str;
	}
	
	public function existsAttr(MineParkPlayer $player, string $key) : bool
	{
		return (preg_match('/'.strtoupper($key).'/', $player->getProfile()->attributes));
	}
	
	public function changeAttr(MineParkPlayer $player, string $key, bool $status = true)
	{
		if(!$status) {
			$player->getProfile()->attributes = str_replace(strtoupper($key), '', $player->getProfile()->attributes);
		} elseif(!$this->existsAttr($player, strtoupper($key))) {
			$player->getProfile()->attributes .= strtoupper($key);
		}

		$this->getCore()->getProfiler()->saveProfile($player);
	}
	
	public function arest(MineParkPlayer $player)
	{
		$this->getCore()->getMapper()->teleportPoint($player, Mapper::POINT_NAME_JAIL);
		$this->getCore()->getApi()->changeAttr($player, self::ATTRIBUTE_ARRESTED);
		$this->getCore()->getApi()->changeAttr($player, self::ATTRIBUTE_WANTED, false);

		$player->getStatesMap()->bar = "§6ВЫ АРЕСТОВАНЫ!";

		$player->setImmobile(false);
	}

	public function interval(int $value, int $from, int $to)
	{
		return ($value >= min($from, $to) and $value <= max($from, $to));
	}

	public function getAdministration(bool $namesOnly = false) : array
	{
		$list = [];

		foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
			if ($player->hasPermission(Permissions::ADMINISTRATOR) or $player->isOp()) {
				$namesOnly ? array_push($list, $player->getName()) : array_push($list, $player);
			}
		}

		return $list;
	}
	
	public function sendToMessagesLog(string $prefix, string $message)
	{
		file_put_contents(Files::MESSAGES_LOG_FILE, (PHP_EOL . "(" . $prefix . ") - " . $message), FILE_APPEND);
	}

	public function removeDefaultServerCommand($commandName)
	{
		$commandMap = $this->getCore()->getServer()->getCommandMap();
		$cmd = $commandMap->getCommand($commandName);
		$cmd->unregister($commandMap);
		$commandMap->unregister($cmd);
	}

	private function getCore() : Core
	{
		return Core::getActive();
	}
}
?>