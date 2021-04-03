<?php
namespace minepark\commands\admin;

use minepark\Providers;

use pocketmine\event\Event;
use minepark\defaults\Sounds;

use minepark\defaults\Defaults;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\organisations\Organisations;
use minepark\components\Phone;
use minepark\components\Tracking;
use minepark\providers\ProfileProvider;

class AdminCommand extends Command
{
    public const CURRENT_COMMAND = "a";

    private Phone $phone;

    private Organisations $organisations;

    private Tracking $tracking;

    private ProfileProvider $profileProvider;

    public function __construct()
    {
        $this->phone = Components::getComponent(Phone::class);
        $this->organisations = Components::getComponent(Organisations::class);
        $this->tracking = Components::getComponent(Tracking::class);
        $this->profileProvider = Providers::getProfileProvider();
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
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
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

            case 'siren':
                $this->commandSiren();
            break;
        }
    }

    public function commandMessage(array $args)
    {
        $text = $this->getCore()->getApi()->getFromArray($args, 1);

        foreach ($this->getCore()->getServer()->getOnlinePlayers() as $p) {
            $num = $this->phone->getNumber($p);
            $this->phone->sendMessage($num, $text, Defaults::CONTEXT_NAME);
        }
    }

    public function commandSetOrg(MineParkPlayer $player, array $args)
    {
        if(!$this->countArguments($player, $args, 2)) {
            return;
        }

        $oid = $args[2];
        $targetPlayer = MineParkPlayer::cast($this->getCore()->getServer()->getPlayer($args[1]));
        
        if($targetPlayer === null) {
            return;
        }

        $targetPlayer->getProfile()->organisation = $oid; 
        $this->profileProvider->saveProfile($targetPlayer);

        $player->sendMessage("AdminCmdSetOrg1");
        $targetPlayer->sendLocalizedMessage("{GroupYou}". $this->organisations->getName($oid));
    }

    public function commandNear(MineParkPlayer $player)
    {
        $rad = 7;
        
        $list = $this->getCore()->getApi()->getRegionPlayers($player, $rad);

        $f = "AdminCmdPlayerNear";
        foreach($list as $p) {
            $f .= " " . $p->getName();
        }
        $player->sendMessage($f);
    }

    public function commandArest(MineParkPlayer $player, array $args)
    {
        if(!$this->countArguments($player, $args, 1)) {
            return;
        }

        $targetPlayer = MineParkPlayer::cast($this->getCore()->getServer()->getPlayer($args[1]));

        if($targetPlayer === null) {
            return;
        }

        $this->getCore()->getApi()->arest($targetPlayer);
        
        $this->getCore()->getServer()->broadcastMessage("{AdminCmdArestPart1}" . $player->getProfile()->fullName . "{AdminCmdArestPart2}" . $targetPlayer->getProfile()->fullName);
    }

    public function commandTags(MineParkPlayer $player, array $args)
    {
        if(!$this->countArguments($player, $args, 1)) {
            return;
        }

        $targetPlayer = MineParkPlayer::cast($this->getCore()->getServer()->getPlayer($args[1]));

        if($targetPlayer === null) {
            return;
        }

        $player->sendMessage($targetPlayer->getProfile()->attributes);
    }

    public function commandAddTag(MineParkPlayer $player, array $args)
    {
        if(!$this->countArguments($player, $args, 2)) {
            return;
        }

        $targetPlayer = $this->getCore()->getServer()->getPlayer($args[1]);

        if($targetPlayer === null or !isset($args[2])) {
            return;
        }

        $this->getCore()->getApi()->changeAttr($targetPlayer, strtoupper($args[2]));

        $player->sendMessage("AdminCmdSetTag");
    }

    public function commandRemoveTag(MineParkPlayer $player, array $args)
    {
        if(!$this->countArguments($player, $args, 2)) {
            return;
        }

        $targetPlayer = $this->getCore()->getServer()->getPlayer($args[1]);
        
        if($targetPlayer === null or !isset($args[2])) {
            return;
        }

        $this->getCore()->getApi()->changeAttr($targetPlayer, strtoupper($args[2]), false);

        $player->sendMessage("AdminCmdRemoveTag");
    }

    public function commandHide(MineParkPlayer $player)
    {
        foreach($this->getCore()->getServer()->getOnlinePlayers() as $p) {
            $p->hidePlayer($player);
        }
        
        $player->sendMessage("AdminCmdHide");
    }

    public function commandShow(MineParkPlayer $player)
    {
        foreach($this->getCore()->getServer()->getOnlinePlayers() as $p) {
            $p->showPlayer($player);
        }
        
        $player->sendMessage("AdminCmdShow");
    }

    public function commandTrack(MineParkPlayer $player, array $args)
    {
        if (!$this->countArguments($player, $args, 1)) {
            return;
        }

        $target = $this->getCore()->getServer()->getPlayer($args[1]);

        if ($target === null) {
            $player->sendMessage("TrackerPlayerNotExists");
            return;
        }

        if ($this->tracking->isTracked($target)) {
            $player->sendMessage("TrackerAlreadyTracked");
            return;
        }

        $this->tracking->enableTrack($target, $player);
    }

    public function commandUnTrack(MineParkPlayer $player, array $args)
    {
        if (!$this->countArguments($player, $args, 1)) {
            return;
        }

        $target = $this->getCore()->getServer()->getPlayer($args[1]);

        if ($target === null) {
            $player->sendMessage("TrackerPlayerNotExists");
            return;
        }

        if (!$this->tracking->isTracked($target)) {
            $player->sendMessage("TrackerAlreadyUnTracked");
            return;
        }

        $this->tracking->disableTrack($target, $player);
    }

    public function commandSiren()
    {
        foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);
            $player->sendSound(Sounds::SIREN_SOUND);
        }
    }

    private function countArguments(MineParkPlayer $player, array $args, int $minCount)
    {
        if(count($args) < $minCount + 1) {
            $player->sendMessage("NoArguments");
            return false;
        }

        return true;
    }
}
?>