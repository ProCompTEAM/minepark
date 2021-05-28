<?php
namespace minepark\models\player;

use pocketmine\level\Position;
use pocketmine\level\particle\FloatingTextParticle;

class FloatingText
{
    public bool $delivered;

    public Position $position;

    public string $text;

    public string $tag;

    public FloatingTextParticle $particle;
}