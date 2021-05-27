<?php
namespace minepark\models\player;

use pocketmine\level\Position;
use minepark\common\player\MineParkPlayer;
use minepark\components\vehicles\models\base\BaseCar;

class StatesMap
{
    public bool $auth;

    public bool $isNew;

    public bool $isBeginner;

    public ?Position $gps;

    public ?string $bar;

    public ?MineParkPlayer $phoneCompanion;

    public ?MineParkPlayer $phoneIncomingCall;

    public ?MineParkPlayer $phoneOutcomingCall;

    public array $goods;

    public ?int $loadWeight;

    public bool $damageDisabled;

    public int $paymentMethod;

    public int $lastTap;

    public ?BaseCar $ridingVehicle;

    public ?BaseCar $rentedVehicle;

    public bool $gpsLightsVisible;

    public ?BossBarSession $bossBarSession;
}