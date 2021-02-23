<?php
namespace minepark\components;

use minepark\utils\CallbackTask;
use minepark\Core;
use minepark\defaults\Permissions;

use minepark\common\player\MineParkPlayer;

class Tracker
{
    public const CHAT_PREFIX = "§b[Track]";

    public $tracked;

	public function __construct()
	{
        $this->tracked = [];
	}
	
	public function getCore() : Core
	{
		return Core::getActive();
	}
    
    public function isTracked(MineParkPlayer $player) : bool
    {
        return isset($this->tracked[$player->getName()]) and $this->tracked[$player->getName()];
    }

    public function enableTrack(MineParkPlayer $player, MineParkPlayer $causer = null)
    {
        $playerName = $player->getName();

        $this->tracked[$playerName] = true;

        if ($causer == null) {
            $this->broadcastAdmins([
                "{TrackerStartNew1}".$playerName."{TrackerStartNew2}",
                "{TrackerStartNew3}".$playerName
            ]);
            return;
        }

        $this->broadcastAdmins([
            "{TrackerStartAdmin1}".$playerName."{TrackerStartAdmin2}".$causer->getName(),
            "{TrackerStartNew3}".$playerName
        ]);
    }

    public function disableTrack(MineParkPlayer $player, MineParkPlayer $causer = null)
    {
        $playerName = $player->getName();

        $this->tracked[$playerName] = false;

        if ($causer == null) {
            $this->broadcastAdmins([
                "{TrackerStopExit1}".$player->getName()."{TrackerStopExit2}"
            ]);
            return;
        }

        $this->broadcastAdmins([
            "{TrackerStopAdmin1}".$playerName."{TrackerStopAdmin2}".$causer->getName(),
            "{TrackerStopAdmin3}".$playerName
        ]);
    }

    public function message(MineParkPlayer $player, string $message, $rad=7, string $prefix = "[UNDEFINED]")
    {
        if (!$this->isTracked($player)) {
            return;
        }

        $playerName = $player->getName();

        $this->broadcastAdmins([
            "$prefix §8 $playerName: $message"
        ], $player);
    }

    public function actionRP(MineParkPlayer $player, string $action, $rad=7, string $prefix = "[UndefinedRP]")
    {
        if (!$this->isTracked($player)) {
            return;
        }

        $playerName = $player->getName();

        $this->broadcastAdmins([
            "$prefix §8 $playerName $action"
        ], $player);
    }

    private function broadcastAdmins(array $messages=[], MineParkPlayer $sender = null, $rad = 7)
    {
        $admins = $this->getCore()->getApi()->getAdministration();

        if(is_null($sender)) {
            foreach($admins as $admin) {
                $this->sendMessage($admin, $messages);
            }
        } else {
            foreach($admins as $admin) {
                if ($sender->distance($admin) > $rad) {
                    continue;
                }
    
                $this->sendMessage($admin, $messages);
            }
        }
    }

    private function sendMessage(MineParkPlayer $player, array $messages = [])
    {
        foreach($messages as $message) {
            $player->sendLocalizedMessage(self::CHAT_PREFIX . $message);
        }
    }
}
?>