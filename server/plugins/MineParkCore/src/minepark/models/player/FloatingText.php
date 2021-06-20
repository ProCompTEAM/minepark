<?php
namespace minepark\models\player;

use pocketmine\world\Position;
use pocketmine\world\particle\FloatingTextParticle;

class FloatingText
{
    public bool $delivered;

    public Position $position;

    public string $text;

    public string $tag;

    public FloatingTextParticle $particle;
}