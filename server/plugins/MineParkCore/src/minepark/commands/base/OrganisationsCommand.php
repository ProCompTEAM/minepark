<?php
namespace minepark\commands\base;

use minepark\Api;
use minepark\common\player\MineParkPlayer;

abstract class OrganisationsCommand extends Command
{
    protected function isBoss(MineParkPlayer $player) : bool
    {
        return $this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_BOSS);
    }
}
?>