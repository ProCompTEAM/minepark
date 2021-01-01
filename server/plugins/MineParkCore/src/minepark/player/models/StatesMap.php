<?php
namespace minepark\player\models;

use minepark\player\implementations\MineParkPlayer;
use pocketmine\level\Position;

class StatesMap
{
	public bool $auth;

	public ?Position $gps;

	public ?string $bar;

	public ?MineParkPlayer $phoneRcv;

	public ?MineParkPlayer $phoneReq;

	public array $goods;

	public ?int $loadWeight;

	public bool $damageDisabled;

	public int $lastTap;
}
?>