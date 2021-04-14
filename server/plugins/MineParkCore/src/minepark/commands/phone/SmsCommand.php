<?php
namespace minepark\commands\phone;

use minepark\defaults\Sounds;

use minepark\common\player\MineParkPlayer;
use minepark\defaults\Permissions;

use pocketmine\event\Event;
use minepark\commands\base\Command;
use minepark\Components;
use minepark\components\phone\Phone;

class SmsCommand extends Command
{
    public const CURRENT_COMMAND = "sms";

    private Phone $phone;

    public function __construct()
    {
        $this->phone = Components::getComponent(Phone::class);
    }

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        array_unshift($args, self::CURRENT_COMMAND);

        $this->phone->cmd($player, $args);

        $player->sendSound(Sounds::ENABLE_PHONE);
    }
}
?>