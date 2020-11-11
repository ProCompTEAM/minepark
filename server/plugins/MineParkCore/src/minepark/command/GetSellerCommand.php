<?php
namespace minepark\command;

use minepark\Sounds;
use pocketmine\Player;
use minepark\Permission;

use pocketmine\event\Event;
use pocketmine\level\Position;

class GetSellerCommand extends Command
{
    public const CURRENT_COMMAND = "getseller";

    public const DISTANCE = 10;

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $player->sendSound(Sounds::ROLEPLAY);

        $shopPoint = $this->getShop($player->getPosition());

        if($shopPoint == null) {
            $player->sendMessage("§cРядом нет торговых точек. Возможно стоит подойти к кассе ближе!");
            return;
        }

        foreach($this->getCore()->getServer()->getOnlinePlayers() as $p){
            if($p->getProfile()->organisation == 5) {
                $p->sendMessage("§eНа торговую площадку §b$shopPoint §eтребуется продавец!");
            }
        }

        $player->sendMessage("§aВы вызвали продавца, пожалуйста ожидайте...");
        $player->sendMessage("§7Попробуйте снова через пару минут, если продавец не пришел к вам!");
    }

    private function getShop(Position $position) : ?string
    {
		$shops = $this->getCore()->getMapper()->getNearPoints($position, self::DISTANCE);
		foreach($shops as $point) {
			if($this->getCore()->getMapper()->getPointGroup($point) == 2) {
                return $point;
            }
        }
        
        return null;
    }
}
?>