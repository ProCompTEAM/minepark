<?php
namespace minepark\models\data;

use minepark\common\player\MineParkPlayer;
use pocketmine\math\Vector3;

class PassengerSeat
{
    public Vector3 $vector;

    public ?MineParkPlayer $passenger;
}