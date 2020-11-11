<?php
namespace minepark\command\admin;

use pocketmine\Player;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\Permission;

class AdminCommand extends Command
{
    public const CURRENT_COMMAND = "a";

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permission::HIGH_ADMINISTRATOR,
            Permission::ADMINISTRATOR
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
		if (self::argumentsNo($args)) {
			$player->sendMessage("§eВсе аргументы пусты :(");
			return;
		}
		
        $command = strtolower($args[0]);

        switch($command) {
            case 'msg':
			case 'sms':
				$this->commandMessage($args);
            break;

			case 'setorg':
				$this->commandSetOrg($player, $args);
            break;

			case 'near':
				$this->commandNear($player);
            break;

			case 'arest':
				$this->commandArest($player, $args);
            break;

			case 'tags':
				$this->commandTags($player, $args);
            break;

			case 'addtag':
				$this->commandAddTag($player, $args);
            break;

			case 'remtag':
				$this->commandRemoveTag($player, $args);
            break;

			case 'hide':
				$this->commandHide($player);
            break;

			case 'show':
				$this->commandShow($player);
            break;
            
            case 'track':
                $this->commandTrack($player, $args);
            break;

            case 'untrack':
                $this->commandUnTrack($player, $args);
            break;
		}
    }

    public function commandMessage(array $args)
    {
        $text = $this->getCore()->getApi()->getFromArray($args, 1);

        foreach($this->getCore()->getServer()->getOnlinePlayers() as $p) {
            $num = $this->getCore()->getPhone()->getNumber($p);
            $this->getCore()->getPhone()->sendMessage($num, $text, $this->getCore()->getApi()->getName());
        }
    }

    public function commandSetOrg(Player $player, array $args)
    {
        if(!$this->countArguments($player, $args, 2)) {
            return;
        }

        $oid = $args[2];
        $targetPlayer = $this->getCore()->getServer()->getPlayer($args[1]);
        
		if($targetPlayer === null) {
            return;
        }

        $targetPlayer->getProfile()->organisation = $oid; 
        $this->getCore()->getInitializer()->updatePlayerSaves($targetPlayer);

		$player->sendMessage("[!] Организация игрока изменена на $oid");
		$targetPlayer->sendMessage("Теперь вы ". $this->getCore()->getOrganisationsModule()->getName($oid));
    }

    public function commandNear(Player $player)
    {
        $rad = 7;
        
        $list = $this->getCore()->getApi()->getRegionPlayers($player->getX(), $player->getY(), $player->getZ(), $rad);

        $f = "Игроки в радиусе $rad блоков:";
        foreach($list as $p) {
            $f .= " " . $p->getName();
        }
        $player->sendMessage($f);
    }

    public function commandArest(Player $player, array $args)
    {
        if(!$this->countArguments($player, $args, 1)) {
            return;
        }

        $targetPlayer = $this->getCore()->getServer()->getPlayer($args[1]);

		if($targetPlayer === null) {
            return;
        }

        $this->getCore()->getApi()->arest($targetPlayer);
        
        $this->getCore()->getServer()->broadcastMessage("§7[§eA§7] §6Администратор " . $player->getProfile()->fullName . " арестовал " . $targetPlayer->getProfile()->fullName);
    }

    public function commandTags(Player $player, array $args)
    {
        if(!$this->countArguments($player, $args, 1)) {
            return;
        }

        $targetPlayer = $this->getCore()->getServer()->getPlayer($args[1]);

		if($targetPlayer === null) {
            return;
        }

		$player->sendMessage($targetPlayer->getProfile()->attributes);
    }

    public function commandAddTag(Player $player, array $args)
    {
        if(!$this->countArguments($player, $args, 2)) {
            return;
        }

        $targetPlayer = $this->getCore()->getServer()->getPlayer($args[1]);

        if($targetPlayer === null or !isset($args[2])) {
            return;
        }

        $this->getCore()->getApi()->changeAttr($targetPlayer, strtoupper($args[2]));

        $player->sendMessage("Тег игрока изменен на <TRUE>!");
    }

    public function commandRemoveTag(Player $player, array $args)
    {
        if(!$this->countArguments($player, $args, 2)) {
            return;
        }

        $targetPlayer = $this->getCore()->getServer()->getPlayer($args[1]);
        
        if($targetPlayer === null or !isset($args[2])) {
            return;
        }

        $this->getCore()->getApi()->changeAttr($targetPlayer, strtoupper($args[2]), false);

        $player->sendMessage("Тег игрока изменен на <FALSE>!");
    }

    public function commandHide(Player $player)
    {
        foreach($this->getCore()->getServer()->getOnlinePlayers() as $p) {
            $p->hidePlayer($player);
        }
        
        $player->sendMessage("Теперь вы невидимый! Возврат: /a show");
    }

    public function commandShow(Player $player)
    {
        foreach($this->getCore()->getServer()->getOnlinePlayers() as $p) {
            $p->showPlayer($player);
        }
        
        $player->sendMessage("Вас снова видят все игроки!");
    }

    public function commandTrack(Player $player, array $args)
    {
        if (!$this->countArguments($player, $args, 1)) {
            return;
        }

        $target = $this->getCore()->getServer()->getPlayer($args[1]);

        if ($target === null) {
            $player->sendMessage("TrackerPlayerNotExists");
            return;
        }

        if ($this->getCore()->getTrackerModule()->isTracked($target)) {
            $player->sendMessage("TrackerAlreadyTracked");
            return;
        }

        $this->getCore()->getTrackerModule()->enableTrack($target, $player);
    }

    public function commandUnTrack(Player $player, array $args)
    {
        if (!$this->countArguments($player, $args, 1)) {
            return;
        }

        $target = $this->getCore()->getServer()->getPlayer($args[1]);

        if ($target === null) {
            $player->sendMessage("TrackerPlayerNotExists");
            return;
        }

        if (!$this->getCore()->getTrackerModule()->isTracked($target)) {
            $player->sendMessage("TrackerAlreadyUnTracked");
            return;
        }

        $this->getCore()->getTrackerModule()->disableTrack($target, $player);
    }

    private function countArguments(Player $player, array $args, int $minCount)
    {
        if(count($args) < $minCount + 1) {
            $player->sendMessage("У этой команды должно быть больше аргументов!");
            return false;
        }

        return true;
    }
}
?>