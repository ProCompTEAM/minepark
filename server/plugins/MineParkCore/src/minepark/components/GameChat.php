<?php
namespace minepark\components;

use minepark\Providers;
use minepark\defaults\ChatConstants;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use pocketmine\event\player\PlayerChatEvent;

class GameChat extends Component
{
    private const CHAT_MESSAGE_PREFIX = "{ChatSaid}";

    private const ROLEPLAY_ACTION_PREFIX = "§d";

    private const SELF_CHAT_PREFIX = "{ChatIAm}";

    public function getAttributes(): array
    {
        return [
        ];
    }

    public function handleMessageEvent(PlayerChatEvent $event)
    {
        $event->setCancelled();

        $player = MineParkPlayer::cast($event->getPlayer());

        if ($player->muted) {
            $player->sendMessage("ChatMute");
            return;
        }

        $message = $event->getMessage();

        if (isset($player->getStatesMap()->phoneRcv)) {
            return $this->handleInCallMessage($player, $message);
        }

        $prefix = $message[0];

        if ($prefix === ChatConstants::GLOBAL_CHAT_SIGNATURE) {
            $this->sendGlobalMessage($player, substr($message, 1));
        } else if ($prefix === ChatConstants::ADMINISTRATION_CHAT_SIGNATURE) {
            $this->sendAdminMessage($player, substr($message, 1));
        } else {
            $this->sendLocalMessage($player, $message, self::CHAT_MESSAGE_PREFIX, ChatConstants::LOCAL_CHAT_HEAR_RADIUS, true);
        }
    }

    public function sendLocalMessage(MineParkPlayer $player, string $message, string $prefix = self::CHAT_MESSAGE_PREFIX, int $radius = ChatConstants::LOCAL_CHAT_HEAR_RADIUS, bool $checkForEmotions = false)
    {
        $senderFullName = $player->getProfile()->fullName;

        if ($checkForEmotions and $this->checkForEmotion($player, $message)) {
            return;
        }

        if ($checkForEmotions and $this->checkMessageSuffix($player, $message)) {
            return;
        }

        $isFriendRequest = $this->isFriendsRequest($senderFullName, $message);

        $randomPrefix = "§7" . $this->getRandomUserPrefix();

        foreach ($this->getCore()->getServer()->getOnlinePlayers() as $onlinePlayer) {
            if ($onlinePlayer->distance($player) <= $radius) {
                $this->sendMessage($onlinePlayer, $message, $player->getLowerCaseName(), $senderFullName, $isFriendRequest, $randomPrefix, $prefix);
            }
        }
    }

    public function sendGlobalMessage(MineParkPlayer $player, string $message)
    {
        if ($player->getStatesMap()->isBeginner) {
            return $player->sendMessage("ChatRestrictBeginner");
        }

        if (!$this->getCore()->getPhone()->hasStream($player)) {
            return $player->sendMessage("ChatNoStream");
        }

        $generatedMessage = "{GlobalMessagePart1}" . $player->getProfile()->fullName . "{GlobalMessagePart2}$message";

        foreach ($this->getCore()->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer = MineParkPlayer::cast($onlinePlayer);

            if ($this->getCore()->getPhone()->hasStream($onlinePlayer)) {
                $onlinePlayer->sendLocalizedMessage($generatedMessage);
            }
        }
    }

    public function sendAdminMessage(MineParkPlayer $player, string $message)
    {
        if (!$player->isAdministrator()) {
            return $player->sendMessage("ChatRestrictAdmin");
        }

        $generatedMessage = "{AdminChatPart1} " . $player->getProfile()->fullName . "{AdminChatPart2}$message";

        foreach ($this->getCore()->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer = MineParkPlayer::cast($onlinePlayer);

            if ($onlinePlayer->isAdministrator()) {
                $onlinePlayer->sendLocalizedMessage($generatedMessage);
            }
        }
    }

    private function handleInCallMessage(MineParkPlayer $player, string $message)
    {
        $this->getCore()->getPhone()->handleInCall($player, $message);
        $this->getCore()->getChatter()->sendLocalMessage($player, $message, "{ChatSpeakPhone}");
        $this->getCore()->getTrackerModule()->message($player, $message, 7, "[PHONE]");
    }

    private function sendMessage(MineParkPlayer $targetPlayer, string $message, string $senderName, string $senderFullName, bool $haveToBeFriends, string $userPrefix, string $chatPrefix)
    {
        if ($targetPlayer->getLowerCaseName() === $senderName) {
            $userPrefix = self::SELF_CHAT_PREFIX;
        } else if (str_contains($targetPlayer->getProfile()->people, $senderName)) {
            $userPrefix = "§7" . $senderFullName;
        } else if ($haveToBeFriends) {
            $userPrefix = "§2" . $senderFullName;

            $targetPlayer->getProfile()->people .= $senderName;

            Providers::getProfileProvider()->saveProfile($targetPlayer);
        }

        $targetPlayer->sendLocalizedMessage($userPrefix . $chatPrefix . " $message");
    }

    private function isFriendsRequest(string $personFullName, string $message) : bool
    {
        $message = mb_strtolower($message);

        foreach ($this->getFriendRequestDictionary($personFullName) as $requestWord) {
            if (str_contains($message, $requestWord)) {
                return true;
            }
        }

        return false;
    }

    private function checkForEmotion(MineParkPlayer $player, string $message) : bool
    {
        if (!isset($this->getEmotionActions()[$message])) {
            return false;
        }

        $emotion = $this->getEmotionActions()[$message];

        $this->sendLocalMessage($player, $emotion, self::ROLEPLAY_ACTION_PREFIX, ChatConstants::ROLEPLAY_ACTION_RADIUS);

        return true;
    }

    private function checkMessageSuffix(MineParkPlayer $player, string $message) : bool
    {
        $suffix = $message[-1];

        if (!isset($this->getEmotionEndingPrefixes()[$suffix])) {
            return false;
        }

        $chatPrefix = $this->getEmotionEndingPrefixes()[$suffix];

        $this->sendLocalMessage($player, substr($message, 0, strlen($message) - 1), $chatPrefix);

        return true;
    }

    private function getRandomUserPrefix()
    {
        return "{ChatUserPrefix" . mt_rand(1, $this->getUserChatPrefixesCount()) . "}";
    }

    private function getUserChatPrefixesCount() : int
    {
        return 11;
    }

    private function getFriendRequestDictionary(string $personName) : array
    {
        return [
            "мое имя", "меня зовут", "звать меня", "my name","мене звати", "mam na imię",
            "mano vardas", "ich heiße", "mon nom est", "Менің атым", "mi chiamo", strtolower($personName)
        ];
    }

    private function getEmotionEndingPrefixes() : array
    {
        return [
            ")" => "{ChatSaidWithSmile}",
            "(" => "{ChatSaidWithSad}"
        ];
    }

    private function getEmotionActions() : array
    {
        return [
            ":)" => "{ChatSmile}",
            ":(" => "{ChatSad}",
            ":/" => "{ChatSurprise}",
            ":D" => "{ChatLaugh}",
        ];
    }
}
?>