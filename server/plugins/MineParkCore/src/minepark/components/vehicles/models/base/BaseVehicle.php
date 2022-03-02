<?php
namespace minepark\components\vehicles\models\base;

use minepark\common\player\MineParkPlayer;
use pocketmine\entity\Entity;

abstract class BaseVehicle extends Entity
{
    abstract public function tryToRemovePlayer(MineParkPlayer $player);
}