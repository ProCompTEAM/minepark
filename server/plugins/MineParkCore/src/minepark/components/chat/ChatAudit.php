<?php
namespace minepark\components\chat;

use minepark\common\player\MineParkPlayer;
use minepark\defaults\ChatConstants;
use minepark\defaults\EventList;
use minepark\Events;
use minepark\components\base\Component;
use minepark\Providers;
use minepark\providers\data\UsersDataProvider;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class ChatAudit extends Component
{
    private UsersDataProvider $usersProvider;

    public function initialize()
    {
        $this->usersProvider = Providers::getUsersDataProvider();

        Events::registerEvent(EventList::PLAYER_COMMAND_PREPROCESS_EVENT, [$this, "handleMessage"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function handleMessage(PlayerCommandPreprocessEvent $event)
    {
        $sender = MineParkPlayer::cast($event->getPlayer());

        if(!$sender->isAuthorized()) {
            return;
        }

        $message = $event->getMessage();

        if($message[0] === ChatConstants::COMMAND_PREFIX) {
            $this->usersProvider->saveExecutedCommand($sender->getName(), substr($message, 1));
        } else {
            $this->usersProvider->saveChatMessage($sender->getName(), $message);
        }
    }
}
?>