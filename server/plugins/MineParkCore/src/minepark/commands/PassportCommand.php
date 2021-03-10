<?php
namespace minepark\commands;

use minepark\Api;
use pocketmine\event\Event;

use minepark\defaults\Sounds;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;

class PassportCommand extends Command
{
    public const CURRENT_COMMAND = "doc";
    public const CURRENT_COMMAND_ALIAS1 = "showpass";
    public const CURRENT_COMMAND_ALIAS2 = "pass";

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
        
        $this->getCore()->getChatter()->send($player, "{CommandPassportTake}", "§d", 10);
    }

    private function getPassportForm(MineParkPlayer $player) : string
    {
        $outputOrg = $this->getCore()->getOrganisationsModule()->getName($player->getProfile()->organisation);
        $outputRank = (($this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_BOSS)) ? " §7[§bНачальник§7]" : "");
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
        foreach($this->getCore()->getApi()->getRegionPlayers($player, 4) as $p) {
			$p->sendWindowMessage($form, "Паспорт " . $player->getName());
				
			if(strpos($p->getProfile()->people, strtolower($player->getName())) === false and $p !== $player) {
				$p->getProfile()->people .= strtolower($player->getName());
				$this->getCore()->getProfiler()->saveProfile($p);
			}
        }
    }
}
?>