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
		
		$list = $this->getCore()->getApi()->getRegionPlayers($player->getX(), $player->getY(), $player->getZ(), 4);
		foreach($list as $p) {
			$p->sendWindowMessage($form, "Паспорт " . $player->fullname);
				
			if(strpos($p->people, strtolower($player->getName())) === false and $p !== $player) {
				$p->people .= strtolower($player->getName());
				$this->getCore()->getInitializer()->updatePlayerSaves($p);
			}
        }
        
        $this->getCore()->getChatter()->send($player, "достал(а) документы из кармана", "§d", 10);
    }

    private function getPassportForm(Player $player) : string
    {
        $outputOrg = $this->getCore()->getOrganisationsModule()->getName($player->org);
        $outputRank = (($this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_BOSS)) ? " §7[§bНачальник§7]" : "");
        $outputPhone = $this->getCore()->getPhone()->getNumber($player);
        
		$f = "§5Паспортные данные | Печать | WorldDoc";
		$f .= "\n§d★ §eИмя§6: §3" . $player->fullname;
		$f .= "\n§d★ §eОрганизация§6: ". $outputOrg . $outputRank;
			
        if(isset($player->subtag)) {
            $f .= $player->subtag == "§f" ? "\n§d★ §fГостевая карта пропуска" : "\n§d★ §eКарта пропуска§6: " . $player->subtag;
        }

        $f .= "\n§d★ §eТелефонный номер§6: " . $outputPhone;

        return $f;
    }
}
?>