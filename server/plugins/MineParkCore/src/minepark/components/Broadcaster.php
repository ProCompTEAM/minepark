<?php
namespace minepark\components;

use minepark\components\base\Component;
use minepark\Core;
use minepark\defaults\Defaults;
use minepark\Providers;
use minepark\providers\LocalizationProvider;
use minepark\utils\CallbackTask;

class Broadcaster extends Component
{
    private $translations;

    public function __construct()
    {
        $this->initializeBroadcastTask();
    }

    public function broadcastMessage()
    {
        $localizationKeys = $this->getMessagesLocalizationKeys();

        $randomMessageKey = $localizationKeys[random_int(0, count($localizationKeys) - 1)];

        foreach ($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            $player->sendMessage($this->getLocalization()->take($player->locale, $randomMessageKey));
        }
    }

    private function getCore() : Core
    {
        return Core::getActive();
    }

    private function getLocalization() : LocalizationProvider
    {
        return Providers::getLocalizationProvider();
    }

    private function getMessagesLocalizationKeys() : array
    {
        return [
            "AutoMessage1",
            "AutoMessage2",
            "AutoMessage3",
            "AutoMessage4",
            "AutoMessage5",
            "AutoMessage6",
            "AutoMessage7",
            "AutoMessage8",
            "AutoMessage9",
            "AutoMessage10"
        ];
    }

    private function initializeBroadcastTask()
    {
        $this->getCore()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "broadcastMessage"]), Defaults::AUTO_BROADCAST_TIMEOUT * 20);
    }
}
?>