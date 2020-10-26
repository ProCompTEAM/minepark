<?php
namespace Lolya\creature;

use pocketmine\entity\projectile\Snowball;

class BulletEntity extends Snowball
{
	public $ammoDamage;

	public function setAmmoDamage($int)
	{
		if (!is_int($int)) return false;
		$this->ammoDamage = $int;
	}
	
	public function getAmmoDamage()
	{
		return $this->ammoDamage;
	}
}
?>