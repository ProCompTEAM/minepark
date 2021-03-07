<?php
namespace minepark\components;

use minepark\components\base\Component;
use minepark\Core;
use minepark\defaults\Defaults;
use minepark\utils\CallbackTask;

class Broadcaster extends Component
{
    private array $localizationKeys;

    private int $localizationKeysMaxIndex;

    private int $localizationKeysCurrentIndex;

    public function __construct()
    {
        $this->localizationKeys = $this->getMessagesLocalizationKeys();
        $this->localizationKeysMaxIndex = count($this->localizationKeys) - 1;
        $this->localizationKeysCurrentIndex = 0;

        $this->initializeBroadcastTask();
    }

    public function broadcastMessage()
    {
        foreach ($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            $player->sendMessage($$this->localizationKeys[$this->localizationKeysCurrentIndex]);
        }

        if($this->localizationKeysCurrentIndex < $this->localizationKeysMaxIndex) {
            $this->localizationKeysCurrentIndex++;
        } else {
            $this->localizationKeysCurrentIndex = 0;
        }
    }

    private function getCore() : Core
    {
        return Core::getActive();
    }

    private function initializeBroadcastTask()
    {
        $this->getCore()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "broadcastMessage"]), Defaults::AUTO_BROADCAST_TIMEOUT * 20);
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
            "AutoMessage10",
            "AutoMessage11",
            "AutoMessage12",
            "AutoMessage13",
            "AutoMessage14",
            "AutoMessage15",
            "AutoMessage16",
            "AutoMessage17",
            "AutoMessage18",
            "AutoMessage19",
            "AutoMessage20",
            "AutoMessage21",
            "AutoMessage22",
            "AutoMessage23",
            "AutoMessage24",
            "AutoMessage25",
            "AutoMessage26",
            "AutoMessage27",
            "AutoMessage28",
            "AutoMessage29",
            "AutoMessage30"
        ];
    }
}
?>