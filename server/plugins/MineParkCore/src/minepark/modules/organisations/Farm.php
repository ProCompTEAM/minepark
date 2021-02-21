<?php
namespace minepark\modules\organisations;

use minepark\Core;
use minepark\Providers;
use pocketmine\entity\Effect;
use pocketmine\level\Position;

use pocketmine\entity\EffectInstance;
use minepark\common\player\MineParkPlayer;

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
			$player->getStatesMap()->bar = "§eДонесите корзину на пункт сбора около фермы"; 
			$player->getStatesMap()->loadWeight = 1; 
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

		$player->getStatesMap()->loadWeight != null ? $this->handleDrop($player) : $player->sendMessage("§cВам необходимо собрать плантации с земли..");
	}
	
	private function handleDrop(MineParkPlayer $player)
	{
		$player->removeAllEffects();

		$this->getCore()->getChatter()->send($player, "высыпал из корзины урожай", "§d ", 12);
		Providers::getBankingProvider()->givePlayerMoney($player, 150);

		$player->getStatesMap()->loadWeight = null; 
		$player->getStatesMap()->bar = null;
	}

	private function playerIsNearWheat(MineParkPlayer $player)
	{
		return $player->getLevel()->getBlockIdAt($player->getX(), $player->getY() - 1, $player->getZ()) == 255;
	}
}
?>