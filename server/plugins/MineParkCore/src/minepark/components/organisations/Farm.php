<?php
namespace minepark\components\organisations;

use minepark\defaults\TimeConstants;
use minepark\Providers;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\world\Position;

use pocketmine\entity\effect\EffectInstance;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\chat\Chat;
use minepark\defaults\ComponentAttributes;
use minepark\providers\BankingProvider;
use minepark\providers\MapProvider;

class Farm extends Component
{
    public const POINT_NAME = "Ферма";

    private BankingProvider $bankingProvider;

    private MapProvider $mapProvider;

    private Chat $chat;

    public function initialize()
    {
        $this->bankingProvider = Providers::getBankingProvider();

        $this->mapProvider = Providers::getMapProvider();

        $this->chat = Components::getComponent(Chat::class);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }

    public function getHarvest(MineParkPlayer $player)
    {
        if(!$this->isPlayerNearWheat($player)) {
            $player->sendMessage("FarmNoNear");
            return;
        }

        $this->giveSlownessEffect($player);

        $this->chat->sendLocalMessage($player, "FarmHarvestIn", "§d : ", 12);
        $player->getStatesMap()->bar = "§eДонесите корзину на пункт сбора около фермы";
        $player->getStatesMap()->loadWeight = 1;
    }

    public function putHarvest(MineParkPlayer $player)
    {
        if(!$this->isPlayerAtFarm($player)) {
            $player->sendMessage("FarmHarvestOutNoNear");
            return;
        }

        isset($player->getStatesMap()->loadWeight) ? $this->handleDrop($player) : $player->sendMessage("FarmNoHarvest");
    }

    private function giveSlownessEffect(MineParkPlayer $player)
    {
        $effect = StringToEffectParser::getInstance()->parse("slowness");
        $instance = new EffectInstance($effect, TimeConstants::ONE_SECOND_TICKS * 9999, 1, true);
        $player->getEffects()->add($instance);
    }
    
    private function handleDrop(MineParkPlayer $player)
    {
        $player->getEffects()->clear();

        $this->chat->sendLocalMessage($player, "FarmHarvestOut", "§d ", 12);
        $this->bankingProvider->givePlayerMoney($player, 150);

        $player->getStatesMap()->loadWeight = null; 
        $player->getStatesMap()->bar = null;
    }

    private function isPlayerAtFarm(MineParkPlayer $player) : bool
    {
        $points = $this->mapProvider->getNearPoints($player->getPosition(), 3);

        return in_array(self::POINT_NAME, $points);
    }

    private function isPlayerNearWheat(MineParkPlayer $player) : bool
    {
        $vector = $player->getLocation()->subtract(0, 1, 0);
        $block = $player->getWorld()->getBlock($vector);

        return $block->getId() === BlockLegacyIds::FARMLAND;
    }
}