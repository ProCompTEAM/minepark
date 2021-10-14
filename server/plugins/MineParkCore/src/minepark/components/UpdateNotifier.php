<?php
namespace minepark\components;

use minepark\common\player\MineParkPlayer;
use minepark\defaults\EventList;
use minepark\Events;
use minepark\components\base\Component;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;

class UpdateNotifier extends Component
{
    public array $notes;

    public function initialize()
    {
        $this->notes = (new Config($this->getServer()->getDataPath() . "release-notes.json", Config::JSON))->getAll();

        Events::registerEvent(EventList::PLAYER_JOIN_EVENT, [$this, "handleJoin"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function handleJoin(PlayerJoinEvent $event)
    {
        $player = MineParkPlayer::cast($event->getPlayer());
        if($player->getProfile()->lastVersionNotified < $this->notes["version"]) {
            $player->sendWindowMessage($this->notes["text"], $this->notes["title"]);
            $player->getProfile()->lastVersionNotified = $this->notes["version"];
        }
    }
}