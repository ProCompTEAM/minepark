<?php

namespace minepark\components\vehicles\models;

use minepark\components\vehicles\models\base\BaseTrain;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Train extends BaseTrain
{
    public static function getNetworkTypeId() : string
    {
        return EntityIds::MINECART;
    }

    public function getOffsetPosition(Vector3 $vector3) : Vector3
    {
        return $vector3->add(0, 1, 0);
    }

    public function getTrainWidth() : float
    {
        return 1.0;
    }

    public function getTrainHeight() : float
    {
        return 1.0;
    }

    public function getDriverSeatPosition() : Vector3
    {
        return new Vector3(0, 1.5, 8.3);
    }

    public function getPassengerSeatsVectors() : array
    {
        return [
            new Vector3(0.7, 1.6, 5.4),
            new Vector3(0.7, 1.6, 4.4),
            new Vector3(0.7, 1.6, 3.8),
            new Vector3(-0.7, 1.6, 5.4),
            new Vector3(-0.7, 1.6, 4.4),
            new Vector3(-0.7, 1.6, 3.8),
            new Vector3(0.7, 1.6, -5.4),
            new Vector3(0.7, 1.6, -4.4),
            new Vector3(0.7, 1.6, -3.8),
            new Vector3(-0.7, 1.6, -5.4),
            new Vector3(-0.7, 1.6, -4.4),
            new Vector3(-0.7, 1.6, -3.8)
        ];
    }
}