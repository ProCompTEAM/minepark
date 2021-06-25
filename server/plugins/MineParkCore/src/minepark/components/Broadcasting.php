<?php
namespace minepark\components;

use minepark\Tasks;
use minepark\defaults\TimeConstants;
use minepark\components\base\Component;

class Broadcasting extends Component
{
    private array $localizationKeys;

    private int $localizationKeysMaxIndex;

    private int $localizationKeysCurrentIndex;

    public function initialize()
    {
        $this->localizationKeys = $this->getMessagesLocalizationKeys();
        $this->localizationKeysMaxIndex = count($this->localizationKeys) - 1;
        $this->localizationKeysCurrentIndex = 0;

        $this->initializeBroadcastTask();
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function broadcastMessage()
    {
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            $player->sendMessage($this->localizationKeys[$this->localizationKeysCurrentIndex]);
        }

        if($this->localizationKeysCurrentIndex < $this->localizationKeysMaxIndex) {
            $this->localizationKeysCurrentIndex++;
        } else {
            $this->localizationKeysCurrentIndex = 0;
        }
    }

    private function initializeBroadcastTask()
    {
        Tasks::registerRepeatingAction(TimeConstants::AUTO_BROADCAST_INTERVAL, [$this, "broadcastMessage"]);
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