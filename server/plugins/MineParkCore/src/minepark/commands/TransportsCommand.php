<?php
namespace minepark\commands;

use minepark\common\player\MineParkPlayer;
use minepark\defaults\Permissions;
use minepark\models\vehicles\Vehicle1;
use pocketmine\event\Event;
use minepark\providers\data\UsersSource;

class TransportsCommand extends Command
{
    public const CURRENT_COMMAND = "t";

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("А где аргумент, слышь?");
            return;
        }
        
        if ($args[0] === "spawn") {
            if (!self::argumentsMin(2, $args)) {
                return $player->sendMessage("Ошибся ты, человек. /t spawn <машина>");;
            }

            if (!$this->spawnCar($player, $args[1])) {
                return $player->sendMessage("Неверное название модели машины!");
            }
        }
    }

    private function spawnCar(MineParkPlayer $player, string $model) : bool
    {
        $entity = null;

        switch($model) {
            case "car1":
                $entity = new Vehicle1($player->getLevel(), Vehicle1::createBaseNBT($player->asVector3()));
            break;
        }

        if (is_null($entity)) {
            return false;
        }

        $entity->spawnToAll();

        return true;
    }
}
?>