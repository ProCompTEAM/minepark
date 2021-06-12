<?php
namespace Lolya;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;

use Lolya\creature\BulletEntity;
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
        $nbt = $this->generateNbt($player);

        $bullet = new BulletEntity($player->getWorld(), $nbt, $player );
        $bullet->setAmmoDamage($damage);

        $bullet->setMotion ($bullet->getMotion()->multiply(5.0));

        $bullet->spawnToAll();
    }
    
    private function generateNbt(Player $player)
    {
        return new CompoundTag ( "", [ 
                "Pos" => new ListTag ( "Pos", [ 
                        new DoubleTag ( "", $player->x ),
                        new DoubleTag ( "", $player->y + $player->getEyeHeight () ),
                        new DoubleTag ( "", $player->z ) ] ),
                "Motion" => new ListTag ( "Motion", [ 
                        new DoubleTag ( "", - \sin ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ),
                        new DoubleTag ( "", - \sin ( $player->pitch / 180 * M_PI ) ),
                        new DoubleTag ( "",\cos ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ) ] ),
                "Rotation" => new ListTag ( "Rotation", [ 
                        new FloatTag ( "", $player->yaw ),
                        new FloatTag ( "", $player->pitch ) ] ) ] );
    }
}
?>