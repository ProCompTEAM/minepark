<?php
namespace minepark\components\organisations;

use minepark\Providers;
use pocketmine\entity\Effect;
use pocketmine\level\Position;

use pocketmine\entity\EffectInstance;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\chat\GameChat;
use minepark\defaults\ComponentAttributes;
use minepark\providers\BankingProvider;
use minepark\providers\MapProvider;

class Farm extends Component
{
    public const POINT_NAME = "Ферма";

    private BankingProvider $bankingProvider;

    private MapProvider $mapProvider;

    private GameChat $gameChat;

    public function initialize()
    {
        $this->bankingProvider = Providers::getBankingProvider();

        $this->mapProvider = Providers::getMapProvider();

        $this->gameChat = Components::getComponent(GameChat::class);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }

    public function from($player)
    {
        if ($this->playerIsNearWheat($player)) {
            $player->addEffect(new EffectInstance(Effect::getEffect(2), 20 * 9999, 1));

            $this->gameChat->sendLocalMessage($player, "§8(§dв корзине собранный урожай |§8)", "§d : ", 12);
            $player->getStatesMap()->bar = "§eДонесите корзину на пункт сбора около фермы"; 
            $player->getStatesMap()->loadWeight = 1; 
        } else {
            $player->sendMessage("§cВы не на ферме, /gps Ферма");
        }
    }

    public function playerIsAtPlace(Position $pos) : bool
    {
        $points = $this->mapProvider->getNearPoints($pos, 3);

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

        $this->gameChat->sendLocalMessage($player, "высыпал из корзины урожай", "§d ", 12);
        $this->bankingProvider->givePlayerMoney($player, 150);

        $player->getStatesMap()->loadWeight = null; 
        $player->getStatesMap()->bar = null;
    }

    private function playerIsNearWheat(MineParkPlayer $player)
    {
        return $player->getLevel()->getBlockIdAt($player->getX(), $player->getY() - 1, $player->getZ()) == 255;
    }
}