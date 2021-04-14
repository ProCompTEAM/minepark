<?php
namespace minepark\commands\base;

use minepark\common\player\MineParkPlayer;
use minepark\defaults\PlayerAttributes;

abstract class OrganisationsCommand extends Command
{
    protected function isBoss(MineParkPlayer $player) : bool
    {
        return $player->existsAttribute(PlayerAttributes::BOSS);
    }
}
?>