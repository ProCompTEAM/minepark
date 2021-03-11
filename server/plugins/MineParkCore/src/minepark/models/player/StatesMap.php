<?php
namespace minepark\models\player;

use minepark\common\player\MineParkPlayer;
use minepark\models\vehicles\BaseVehicle;
use pocketmine\level\Position;

class StatesMap
{
	public bool $auth;

	public bool $isNew;

	public bool $isBeginner;

	public ?Position $gps;

	public ?string $bar;

	public ?MineParkPlayer $phoneRcv;

	public ?MineParkPlayer $phoneReq;

	public array $goods;

	public ?int $loadWeight;

	public bool $damageDisabled;

	public int $paymentMethod;

	public int $lastTap;

	public ?BaseVehicle $ridingVehicle;

	public ?BaseVehicle $rentedVehicle;
}
?>