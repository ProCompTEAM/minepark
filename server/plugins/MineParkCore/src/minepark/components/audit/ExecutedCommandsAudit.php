<?php
namespace minepark\components\audit;

use minepark\defaults\ChatConstants;
use minepark\defaults\EventList;
use minepark\Events;
use minepark\components\base\Component;
use minepark\Providers;
use minepark\providers\data\UsersDataProvider;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class ExecutedCommandsAudit extends Component
{
    private UsersDataProvider $usersProvider;

    public function initialize()
    {
        $this->usersProvider = Providers::getUsersDataProvider();
        Events::registerEvent(EventList::PLAYER_COMMAND_PREPROCESS_EVENT, [$this, "handleCommand"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function handleCommand(PlayerCommandPreprocessEvent $event)
    {
        $sender = $event->getPlayer();
        $message = $event->getMessage();

        if($message[0] === ChatConstants::COMMAND_PREFIX) {
            $this->usersProvider->executeCommand($sender->getName(), substr($message, 1));
        }
    }
}
?>