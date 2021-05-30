<?php
namespace minepark\commands;

use minepark\Providers;

use pocketmine\event\Event;
use minepark\defaults\Sounds;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\chat\Chat;
use minepark\components\organisations\Organisations;
use minepark\defaults\PlayerAttributes;
use minepark\providers\ProfileProvider;

class PassportCommand extends Command
{
    public const CURRENT_COMMAND = "doc";
    public const CURRENT_COMMAND_ALIAS1 = "showpass";
    public const CURRENT_COMMAND_ALIAS2 = "pass";

    private ProfileProvider $profileProvider;

    private Chat $chat;

    private Organisations $organisations;

    public function __construct()
    {
        $this->profileProvider = Providers::getProfileProvider();

        $this->chat = Components::getComponent(Chat::class);

        $this->organisations = Components::getComponent(Organisations::class);
    }

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND,
            self::CURRENT_COMMAND_ALIAS1,
            self::CURRENT_COMMAND_ALIAS2
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
        $player->sendSound(Sounds::PASSPORT_OPEN);

        $form = $this->getPassportForm($player);
        
        $this->showPassportForm($player, $form);
        
        $this->chat->sendLocalMessage($player, "{CommandPassportTake}", "§d", 10);
    }

    private function getPassportForm(MineParkPlayer $player) : string
    {
        $outputOrg = $this->organisations->getName($player->getProfile()->organisation);
        $outputRank = $player->existsAttribute(PlayerAttributes::BOSS) ? " §7[§bНачальник§7]" : "";
        $outputPhone = $player->getProfile()->phoneNumber;
        
        $form = "§5Паспортные данные | Печать | WorldDoc";
        $form .= "\n§d★ §eПолное имя§6: §3" . $player->getProfile()->fullName
             . "(" . $player->getName() . ")";
        $form .= "\n§d★ §eОрганизация§6: ". $outputOrg . $outputRank;
            
        if(isset($player->subtag)) {
            $form .= $player->subtag == "§f" ? "\n§d★ §fГостевая карта пропуска" : "\n§d★ §eКарта пропуска§6: " . $player->subtag;
        }

        $form .= "\n§d★ §eТелефонный номер§6: " . $outputPhone;

        return $form;
    }

    private function showPassportForm(MineParkPlayer $player, string $form)
    {
        foreach($this->getCore()->getRegionPlayers($player, 4) as $p) {
            $p->sendWindowMessage($form, "Паспорт " . $player->getName());
                
            if(strpos($p->getProfile()->people, strtolower($player->getName())) === false and $p !== $player) {
                $p->getProfile()->people .= strtolower($player->getName());
                $this->profileProvider->saveProfile($p);
            }
        }
    }
}