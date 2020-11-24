<?php
namespace minepark\modules;

use minepark\utils\CallbackTask;
use minepark\Core;
use minepark\Permissions;

use pocketmine\Player;

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
    
    public function isTracked(Player $player) : bool
    {
        return $this->tracked[$player->getName()] == true;
    }

    public function enableTrack(Player $player, Player $causer = null)
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

    public function disableTrack(Player $player, Player $causer=null)
    {
        $playerName = $player->getName();

        $this->tracked[$player->getName()] = false;

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

    public function message(Player $player, string $message, $rad=7, string $prefix="[UNDEFINED]")
    {
        if (!$this->isTracked($player)) {
            return;
        }

        $playerName = $player->getName();

        $this->broadcastAdmins([
            "$prefix §8 $playerName: $message"
        ], $player);
    }

    public function actionRP(Player $player, string $action, $rad=7, string $prefix="[UndefinedRP]")
    {
        if (!$this->isTracked($player)) {
            return;
        }

        $playerName = $player->getName();

        $this->broadcastAdmins([
            "$prefix §8 $playerName $action"
        ], $player);
    }

    private function broadcastAdmins(array $messages=[], Player $sender=null, $rad=7)
    {
        $admins = $this->getAdmins();

        foreach($admins as $admin) {
            if ($sender != null) {
                if ($this->playersNear($sender, $admin, $rad)) {
                    continue;
                }
            }

            $this->sendMessage($admin, $messages);
        }
    }

    private function sendMessage(Player $player, array $messages=[])
    {
        foreach($messages as $message) {
            $player->sendLocalizedMessage(self::CHAT_PREFIX.$message);
        }
    }

    private function getAdmins() : array
    {
        $admins = [];

        foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            if ($player->hasPermission(Permissions::ADMINISTRATOR)) {
                $admins[] = $player;
            }
        }

        return $admins;
    }

    private function playersNear($sender, $player, $rad=7) : bool
    {
        $p_x = $sender->getX();
		$p_y = $sender->getY();
        $p_z = $sender->getZ();
        
        $x1 = $player->getX();
        $y1 = $player->getY();
        $z1 = $player->getZ();

		$x = $x1 - $p_x;
		$z = $z1 - $p_z;
		$y = $y1 - $p_y;

		$x = floor($x);
		$z = floor($z);
		$y = floor($y);

		if($x < $rad and $z < $rad and $x > $rad*-1 and $z > $rad*-1 and $y < $rad and $y > $rad*-1) {
			return true;
        }
        return false;
    }
}
?>