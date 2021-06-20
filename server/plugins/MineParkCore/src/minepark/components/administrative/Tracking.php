<?php
namespace minepark\components\administrative;

use minepark\Events;
use minepark\Providers;
use minepark\defaults\EventList;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;
use pocketmine\event\player\PlayerQuitEvent;
use minepark\providers\data\UsersDataProvider;

class Tracking extends Component
{
    private const CHAT_PREFIX = "§b[Track]";

    private UsersDataProvider $usersProvider;

    public array $tracked = [];

    public function initialize()
    {
        Events::registerEvent(EventList::PLAYER_QUIT_EVENT, [$this, "processPlayerQuitEvent"]);

        $this->usersProvider = Providers::getUsersDataProvider();

        $this->tracked = [];
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function processPlayerQuitEvent(PlayerQuitEvent $event)
    {
        if ($this->isTracked($event->getPlayer())) {
            $this->disableTrack($event->getPlayer());
        }
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

    public function actionRP(MineParkPlayer $player, string $action, int $distance = 7, string $prefix = "[UndefinedRP]")
    {
        $this->saveMessage($player, $prefix, $action);

        if (!$this->isTracked($player)) {
            return;
        }

        $playerName = $player->getName();

        $this->broadcastAdmins([
            "$prefix §8 $playerName $action"
        ], $player, $distance);
    }

    private function broadcastAdmins(array $messages = [], MineParkPlayer $sender = null, int $distance = 7)
    {
        $admins = $this->getCore()->getAdministration();

        if(is_null($sender)) {
            foreach($admins as $admin) {
                $this->sendMessage($admin, $messages);
            }
        } else {
            foreach($admins as $admin) {
                if ($sender->getLocation()->distance($admin->getLocation()) > $distance) {
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

    private function saveMessage(MineParkPlayer $player, string $prefix, string $message)
    {
        $this->usersProvider->saveChatMessage($player->getName(), $prefix . " " . $message);
    }
}