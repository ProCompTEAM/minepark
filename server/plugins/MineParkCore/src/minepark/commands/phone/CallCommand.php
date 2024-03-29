<?php
namespace minepark\commands\phone;

use minepark\defaults\Sounds;

use minepark\common\player\MineParkPlayer;
use minepark\defaults\Permissions;

use pocketmine\event\Event;
use minepark\commands\base\Command;
use minepark\Components;
use minepark\components\chat\Chat;
use minepark\components\phone\Phone;

class CallCommand extends Command
{
    public const CURRENT_COMMAND = "c";

    private Phone $phone;

    private Chat $chat;

    public function __construct()
    {
        $this->phone = Components::getComponent(Phone::class);

        $this->chat = Components::getComponent(Chat::class);
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
        $this->chat->sendLocalMessage($player, "{CommandCallPhoneTake}", "§d : ", 10);

        $player->sendSound(Sounds::ENABLE_PHONE, null, 20);

        if(self::argumentsNo($args)) {
            $this->phone->sendDisplayMessages($player);
            return;
        }

        if (!isset($args[0])) {
            $player->sendMessage("PhoneCheckNum");
        } elseif(is_numeric($args[0])) {
            $this->phone->initializeCallRequest($player, $args[0]);
        } else {
            $this->phone->acceptOrEndCall($player, $args[0]);
        }
    }
}