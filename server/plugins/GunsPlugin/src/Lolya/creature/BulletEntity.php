<?php
namespace Lolya\creature;

use pocketmine\entity\projectile\Snowball;

class BulletEntity extends Snowball
{
    public $ammoDamage;

    public function setAmmoDamage(int $int)
    {
        $this->ammoDamage = $int;
    }
    
    public function getAmmoDamage()
    {
        return $this->ammoDamage;
    }
}
?>