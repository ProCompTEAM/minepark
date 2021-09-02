<?php
namespace Lolya;

use Lolya\creature\BulletEntity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Shoot
{
    public $main;
    
    public function __construct($mainClass)
    {
        $this->main = $mainClass;
    }
    
    public function execute(Player $player, $damage=3)
    {
        $location = $player->getLocation();

        $location->y += $player->getEyeHeight();

        $bullet = new BulletEntity($location, $player);
        $bullet->setMotion($this->getMotion($player));
        $bullet->saveNBT();
        $bullet->setAmmoDamage($damage);

        $bullet->spawnToAll();

        $bullet->setMotion($bullet->getMotion()->multiply(5.0));
    }
    
    private function getMotion(Player $player) : Vector3
    {
        $motion = new Vector3(0.0, 0.0, 0.0);

        $location = $player->getLocation();

        $motion->x = - sin ($location->yaw / 180 * M_PI ) * cos($location->pitch / 180 * M_PI);
        $motion->y = - sin($location->pitch / 180 * M_PI);
        $motion->z =   cos($location->yaw / 180 * M_PI ) * cos($location->pitch / 180 * M_PI);

        return $motion;
    }
}
?>