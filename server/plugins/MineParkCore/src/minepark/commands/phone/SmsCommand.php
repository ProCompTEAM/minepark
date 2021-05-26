<?php
namespace minepark\commands\phone;

use minepark\defaults\Sounds;

use minepark\common\player\MineParkPlayer;
use minepark\defaults\Permissions;

use pocketmine\event\Event;
use minepark\commands\base\Command;
use minepark\Components;
use minepark\components\phone\Phone;
use minepark\utils\ArraysUtility;

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
        $player->sendSound(Sounds::ENABLE_PHONE);

        if(self::argumentsNo($args)) {
            $this->phone->sendDisplayMessages($player);
        } elseif(self::argumentsMin(2, $args) and is_numeric($args[0])) {
            $this->phone->sendSms($player, $args[0], ArraysUtility::getStringFromArray($args, 1));
        } else {
            $player->sendMessage("PhoneCheckNum");
        }
    }
}
?>