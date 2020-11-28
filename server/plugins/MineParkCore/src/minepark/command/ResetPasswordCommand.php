<?php
namespace minepark\command;

use pocketmine\Player;
use minepark\Permissions;

use pocketmine\event\Event;
use minepark\mdc\sources\UsersSource;

class ResetPasswordCommand extends Command
{
    public const CURRENT_COMMAND = "resetpassword";

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

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("Необходимо указать никнейм игрока.");
            return;
        }
        
        $targetPlayerName = $args[0];

        if(!$this->getRemoteSource()->isUserExist($targetPlayerName)){
            $player->sendMessage("Указанного игрока не существует.");
            return;
        }
        
        $this->resetPassword($player, $targetPlayerName);
    }
    
    private function getRemoteSource() : UsersSource
    {
        return $this->getCore()->getMDC()->getSource("users");
    }

    private function resetPassword(Player $sender, string $targetPlayerName)
    {
        $this->getRemoteSource()->resetUserPassword($targetPlayerName);
        $sender->sendMessage("Сброс пароля игрока $targetPlayerName прошёл успешно.");
        $targetPlayer = $this->getCore()->getServer()->getPlayer($targetPlayerName);

        if($targetPlayer != null) { 
            $targetPlayer->kick("§cАдминистратор сбросил вам пароль.");
        }
    }
}
?>