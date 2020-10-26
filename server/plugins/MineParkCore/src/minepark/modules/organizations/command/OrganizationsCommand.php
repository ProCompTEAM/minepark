<?php
namespace minepark\modules\organizations\command;

use minepark\Core;
use minepark\database\model\Model;

use pocketmine\Player;
use pocketmine\event\Event;

abstract class OrganizationsCommand
{
    static public function argumentsNo(array $args) : bool
    {
        return !isset($args[0]);
    }

    static public function argumentsCount(int $count, array $args) : bool
    {
        return count($args) == $count;
    }

    static public function argumentsMin(int $count, array $args) : bool
    {
        return count($args) >= $count;
    }

    static public function argumentsInterval(int $minCount, int $maxCount, array $args) : bool
    {
        return count($args) >= $minCount and count($args) <= $maxCount;
    }

    public const ARGUMENTS_SEPERATOR = " ";

    abstract public function getCommand() : array;

    abstract public function getPermissions() : array;

    abstract public function execute(Player $player, array $args = array(), Event $event = null);

    protected function getCore()
    {
        return Core::getActive();
    }

    protected function getDataFrom(string $modelName) : ?Model 
    {
        return Core::getDatabase()->from($modelName);
    }
}
?>