<?php
namespace minepark\commands;

use minepark\modules\organisations\Organisations;
use minepark\defaults\Sounds;
use minepark\player\implementations\MineParkPlayer;
use minepark\defaults\Permissions;

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
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        $player->sendSound(Sounds::ROLEPLAY);

        $shopPoint = $this->getShop($player->getPosition());

        if($shopPoint == null) {
            $player->sendMessage("CommandGetSellerNoPoint");
            return;
        }

        foreach($this->getCore()->getServer()->getOnlinePlayers() as $targetPlayer){
            $targetPlayer = MineParkPlayer::cast($targetPlayer);
            if($targetPlayer->getProfile()->organisation == Organisations::SELLER_WORK) {
                $targetPlayer->sendMessage("CommandGetSellerCall1");
            }
        }

        $player->sendMessage("CommandGetSellerCall2");
        $player->sendMessage("CommandGetSellerCall3");
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