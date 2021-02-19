<?php
declare(strict_types = 1);

/**
 * @name BasicAddon
 * @version 1.0.0
 * @main    JackMD\ScoreHud\Addons\BasicAddon
 */

namespace JackMD\ScoreHud\Addons
{
	use JackMD\ScoreHud\addon\AddonBase;
	use minepark\Core;
    use minepark\player\Bank;
    use minepark\utils\CallbackTask;
	use onebone\economyapi\EconomyAPI;
	use pocketmine\Player;

	class BasicAddon extends AddonBase{

		/**
		 * @param Player $player
		 * @return array
		 */
		/*public function getProcessedTags(Player $player): array{
			return [
				"{name}"               => $player->getName(),
				"{online}"             => count($player->getServer()->getOnlinePlayers()),
				"{max_online}"         => $player->getServer()->getMaxPlayers(),
				"{item_name}"          => $player->getInventory()->getItemInHand()->getName(),
				"{item_id}"            => $player->getInventory()->getItemInHand()->getId(),
				"{item_meta}"          => $player->getInventory()->getItemInHand()->getDamage(),
				"{item_count}"         => $player->getInventory()->getItemInHand()->getCount(),
				"{x}"                  => intval($player->getX()),
				"{y}"                  => intval($player->getY()),
				"{z}"                  => intval($player->getZ()),
				"{load}"               => $player->getServer()->getTickUsage(),
				"{tps}"                => $player->getServer()->getTicksPerSecond(),
				"{level_name}"         => $player->getLevel()->getName(),
				"{level_folder_name}"  => $player->getLevel()->getFolderName(),
				"{ip}"                 => $player->getAddress(),
				"{ping}"               => $player->getPing(),
				"{time}"               => date($this->getScoreHud()->getConfig()->get("time-format")),
				"{date}"               => date($this->getScoreHud()->getConfig()->get("date-format")),
				"{world_player_count}" => count($player->getLevel()->getPlayers())
			];
		}*/

		private $core;
		private $money = [];

		public function onEnable(): void{
			$this->core = $this->getServer()->getPluginManager()->getPlugin("MineParkCore");
			$callbackTask = new CallbackTask([$this, "updatePlayersMoney"]);
			$this->core->getScheduler()->scheduleRepeatingTask($callbackTask, 20 * 6);
		}
		
		public function getProcessedTags(Player $player) : array
		{
			return [
				"{line1}" => $this->getTranslation($player, "ScoreBoard1") . count($player->getServer()->getOnlinePlayers()),
				"{line2}" => $this->getTranslation($player, "ScoreBoard2") . date($this->getScoreHud()->getConfig()->get("time-format")),
				"{line3}" => $this->getTranslation($player, "ScoreBoard3") . $this->getMoney($player),
				"{line4}" => $this->getTranslation($player, "ScoreBoard4"),
				"{line5}" => $this->getTranslation($player, "ScoreBoard5"),
				"{line6}" => $this->getTranslation($player, "ScoreBoard6"),
				"{line7}" => $this->getTranslation($player, "ScoreBoard7"),
				"{line8}" => $this->getTranslation($player, "ScoreBoard8"),
				"{line9}" => $this->getTranslation($player, "ScoreBoard9")
			];
		}
		
		public function getTranslation(Player $player, string $key) : ?string
		{
			return $this->getCore()->getLocalizer()->take($player->locale, $key);
		}
		
		public function getMoney(Player $player) : float
		{
			if (!isset($this->money[$player->getName()])) {
				$this->money[$player->getName()] = $this->core->getBank()->getAllMoney($player);
			}

			return $this->money[$player->getName()];
		}

		public function updatePlayersMoney($value)
		{
			foreach ($this->getServer()->getOnlinePlayers() as $player) {
				$money[$player->getName()] = $this->core->getBank()->getAllMoney($player);
			}
		}

		protected function getCore() : Core
		{
			return Core::getActive();
		}

		protected function getBank() : Bank
		{
			return $this->getCore()->getBank();
		}
	}
}
