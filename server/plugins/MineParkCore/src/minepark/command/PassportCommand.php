<?php
namespace minepark\command;

use minepark\Api;
use minepark\Permission;

use pocketmine\Player;
use pocketmine\event\Event;
use minepark\Sounds;

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
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $player->sendSound(Sounds::PASSPORT_OPEN);

        $form = $this->getPassportForm($player);
		
		$list = $this->getCore()->getApi()->getRegionPlayers($player, 4);
		foreach($list as $p) {
			$p->sendWindowMessage($form, "Паспорт " . $player->getName());
				
			if(strpos($p->getProfile()->people, strtolower($player->getName())) === false and $p !== $player) {
				$p->getProfile()->people .= strtolower($player->getName());
				$this->getCore()->getProfiler()->saveProfile($p);
			}
        }
        
        $this->getCore()->getChatter()->send($player, "достал(а) документы из кармана", "§d", 10);
    }

    private function getPassportForm(Player $player) : string
    {
        $outputOrg = $this->getCore()->getOrganisationsModule()->getName($player->getProfile()->organisation);
        $outputRank = (($this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_BOSS)) ? " §7[§bНачальник§7]" : "");
        $outputPhone = $this->getCore()->getPhone()->getNumber($player);
        
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
}
?>