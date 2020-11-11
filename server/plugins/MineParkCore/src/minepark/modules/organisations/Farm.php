<?php
namespace minepark\modules\organisations;

use pocketmine\Player;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Effect;
use pocketmine\level\Position;

use minepark\Core;

class Farm
{
	public $core;

	const POINT_NAME = "Ферма";
	
	protected function getCore() : Core
	{
		return Core::getActive();
	}

	public function from($player)
	{
		if ($this->playerIsNearWheat($player)) {
			$player->addEffect(new EffectInstance(Effect::getEffect(2), 20 * 9999, 1));

			$this->core->getChatter()->send($player, "§8(§dв корзине собранный урожай |§8)", "§d : ", 12);
			$player->bar = "§eДонесите корзину на пункт сбора около фермы"; $player->wbox = 1; 
		} else {
			$player->sendMessage("§cВы не на ферме, /gps Ферма");
		}
	}

	public function playerIsAtPlace(Position $pos) : bool
	{
		$points = $this->getCore()->getMapper()->getNearPoints($pos, 3);

		return in_array(self::POINT_NAME, $points);
	}
	
	public function to($player)
	{
		$hasPoint = $this->playerIsAtPlace($player->getPosition());

		if(!$hasPoint) {
			$player->sendMessage("§cВам стоит подойти ближе к точке выброса урожая!");
			return;
		}

		$player->wbox != null ? $this->handleDrop($player) : $player->sendMessage("§cВам необходимо собрать плантации с земли..");
	}
	
	private function handleDrop(Player $player)
	{
		$player->removeAllEffects();

		$this->getCore()->getChatter()->send($player, "высыпал из корзины урожай", "§d ", 12);
		$this->getCore()->getBank()->givePlayerMoney($player, 150);

		$player->wbox = null; $player->bar = null;
	}

	private function playerIsNearWheat(Player $p)
	{
		return $p->getLevel()->getBlockIdAt($p->getX(), $p->getY() - 1, $p->getZ()) == 255;
	}
}
?>