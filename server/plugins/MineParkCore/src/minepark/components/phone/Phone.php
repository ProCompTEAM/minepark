<?php
namespace minepark\components\phone;

use Exception;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\base\Component;
use minepark\components\chat\GameChat;
use minepark\components\organisations\Organisations;
use minepark\defaults\ComponentAttributes;
use minepark\defaults\EventList;
use minepark\defaults\MapConstants;
use minepark\defaults\TimeConstants;
use minepark\Events;
use minepark\models\dtos\BalanceDto;
use minepark\Providers;
use minepark\providers\data\PhonesDataProvider;
use minepark\providers\MapProvider;
use minepark\Tasks;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;

class Phone extends Component
{
    private const MAX_STREAM_DISTANCE = 200;

    private const EMERGENCY_NUMBER_POLICE = 02;

    private const EMERGENCY_NUMBER_AMBULANCE = 03;

    private PhonesDataProvider $phonesDataProvider;

    private MapProvider $mapProvider;

    private GameChat $gameChat;

    public function initialize()
    {
        $this->phonesDataProvider = Providers::getPhonesDataProvider();

        $this->mapProvider = Providers::getMapProvider();

        $this->gameChat = Components::getComponent(GameChat::class);

        Events::registerEvent(EventList::PLAYER_QUIT_EVENT, [$this, "playerQuitEvent"]);
        Tasks::registerRepeatingAction(TimeConstants::PHONE_TAKE_FEE_INTERVAL, [$this, "takeFee"]);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function playerQuitEvent(PlayerQuitEvent $event)
    {
        $player = MineParkPlayer::cast($event->getPlayer());

        if(isset($player->getStatesMap()->phoneSnd)) {
            $player->getStatesMap()->phoneSnd->getStatesMap()->phoneReq = null;
        }

        if(isset($player->getStatesMap()->phoneReq)) {
            $player->getStatesMap()->phoneReq->getStatesMap()->phoneSnd = null;
        }

        if(isset($player->getStatesMap()->phoneRcv)) {
            $this->breakCall($player->getStatesMap()->phoneRcv);
        }
    }

    public function takeFee()
    {
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);

            if(!isset($player->getStatesMap()->phoneRcv)) {
                continue;
            }

            if(!$this->hasStream($player->getStatesMap()->phoneRcv->asPosition())) {
                $this->breakCallForNoStream($player);
            } elseif(!$this->reduceBalance($player, 20)) {
                $this->breakCallForNoMoney($player);
            }
        }
    }

    private function breakCallForNoStream(MineParkPlayer $player)
    {
        $player->sendMessage("PhoneSmsNoNet");
        $player->sendMessage("PhoneSmsErrorNet");

        $player->getStatesMap()->phoneRcv->sendMessage("PhoneSmsErrorNet");

        $player->getStatesMap()->phoneRcv->getStatesMap()->phoneRcv = null; 
        $player->getStatesMap()->phoneRcv = null;
    }

    private function breakCallForNoMoney(MineParkPlayer $player)
    {
        $player->sendMessage("PhoneSmsContinueNoMoney");
        $player->sendMessage("PhoneSmsErrorNet");

        $player->getStatesMap()->phoneRcv->sendMessage("PhoneSmsErrorNet");

        $player->getStatesMap()->phoneRcv->getStatesMap()->phoneRcv = null; 
        $player->getStatesMap()->phoneRcv = null;
    }

    public function getPlayerByNumber(int $number, bool $nameOnly = false) : ?MineParkPlayer
    {
        $userName = $this->phonesDataProvider->getUserNameByNumber($number);

        if(isset($userName)) {
            return $nameOnly ? $userName : $this->getServer()->getPlayer($userName);
        }

        return null;
    }

    public function getNumberByName(string $userName) : ?int
    {
        return $this->phonesDataProvider->getNumberForUser($userName);
    }

    public function hasStream(Position $position)
    {
        return $this->mapProvider->hasNearPointWithType($position, self::MAX_STREAM_DISTANCE, MapConstants::POINT_GROUP_STREAM);
    }

    public function initializeCallRequest(MineParkPlayer $initializer, int $targetNumber)
    {
        if($this->checkForEmergencyNumber($initializer, $targetNumber)) {
            return;
        }

        if(!$this->hasStream($initializer->asPosition())) {
            $initializer->sendMessage("PhoneSmsNoNet2");
            return;
        }

        $target = $this->getPlayerByNumber($targetNumber);

        if(!isset($target)) {
            $initializer->sendMessage("PhoneSmsNoNet");
            return;
        }

        if($target->getName() === $initializer->getName()) {
            $initializer->sendMessage("PhoneCheckNum");
            return;
        }

        $initializer->sendMessage("PhoneBeeps");

        if(isset($initializer->getStatesMap()->phoneRcv) or isset($initializer->getStatesMap()->phoneReq)
            or isset($initializer->getStatesMap()->phoneSnd)) {
            $initializer->sendMessage("PhoneAlreadyInCall");
            return;
        }

        if(isset($target->getStatesMap()->phoneRcv) or isset($target->getStatesMap()->phoneReq)
            or isset($target->getStatesMap()->phoneSnd)) {
            $initializer->sendMessage("PhoneCalling3");
            return;
        }

        $target->getStatesMap()->phoneReq = $initializer;
        $initializer->getStatesMap()->phoneSnd = $target;

        $this->gameChat->sendLocalMessage($target, "{PhoneCallingBeep}", "§d : ", 10);

        $target->sendLocalizedMessage("{PhoneCalling1}" . $initializer->getProfile()->phoneNumber . ".");
        $target->sendMessage("PhoneCalling2");
        $target->sendMessage("PhoneCalling4");

        $initializer->sendMessage("PhoneCalling5");
    }

    public function sendSms(MineParkPlayer $sender, int $targetNumber, string $text)
    {
        if(!$this->hasStream($sender->asPosition())) {
            $sender->sendMessage("PhoneSmsError");
            return;
        }

        $target = $this->getPlayerByNumber($targetNumber);

        if(!isset($target)) {
            $sender->sendMessage("PhoneSmsNoNet");
            return;
        }

        if($target->getName() === $sender->getName()) {
            $sender->sendMessage("PhoneCheckNum");
            return;
        }

        if(!$this->reduceBalance($sender, 20)) {
            $sender->sendMessage("PhoneSmsNoMoney");
            return;
        }

        $target->sendLocalizedMessage("{PhoneSend}" . $sender->getProfile()->phoneNumber);
        $target->sendMessage("§b[➪] " . $text);

        $sender->sendMessage("PhoneSmsSuccess");
    }

    public function acceptOrEndCall(MineParkPlayer $player, string $method)
    {
        if($method === "accept" and isset($player->getStatesMap()->phoneReq)) {
            $this->acceptCall($player);
        } elseif($method === "end" and isset($player->getStatesMap()->phoneRcv)) {
            $this->endCall($player);
        } elseif($method === "cancel" and isset($player->getStatesMap()->phoneSnd)) {
            $this->cancelRequest($player);
        } elseif($method === "reject" and isset($player->getStatesMap()->phoneReq)) {
            $this->rejectCall($player);
        } else {
            $player->sendMessage("PhoneCallReload");
        }
    }

    public function sendDisplayMessages(MineParkPlayer $player)
    {
        $message  = "§9☏ Позвонить: §e/c <номер телефона>\n";
        $message .= "§9☏ Служба Охраны: §e/c 02\n";
        $message .= "§9☏ Мед. помощь: §e/c 03\n";
        $message .= "§9☏ Сообщения: §e/sms <н.телефона> <текст>\n";
        $message .= "§1> Цены: §aСМС 20р, Звонок 20р минута\n";
        $message .= "§1> Ваш телефонный номер: §3" . $player->getProfile()->phoneNumber;

        $player->sendWindowMessage($message, "§9❖======*Смартфон*=======❖");
    }

    public function handleMessage(MineParkPlayer $player, string $message)
    {
        $number = $player->getProfile()->phoneNumber;

        $player->getStatesMap()->phoneRcv->sendMessage("§9✆ §e$number §6: §a".$message);
        $player->sendMessage("§9✆ §5$number §6: §2".$message);
    }

    public function getBalance(MineParkPlayer $player) : float
    {
        return $this->phonesDataProvider->getBalance($player->getName());
    }

    public function addBalance(MineParkPlayer $player, float $amount) : bool
    {
        return $this->phonesDataProvider->addBalance($player->getName(), $amount);
    }

    public function reduceBalance(MineParkPlayer $player, float $amount) : bool
    {
        return $this->phonesDataProvider->reduceBalance($player->getName(), $amount);
    }

    private function acceptCall(MineParkPlayer $player)
    {
        $target = $player->getStatesMap()->phoneReq;

        if(!isset($target->getStatesMap()->phoneSnd)) {
            throw new Exception("Target doesn't have property");
        }

        $player->sendLocalizedMessage("{PhoneCall1}" . $target->getProfile()->phoneNumber . ".."); 
        $player->sendMessage("PhoneCall2");
        $player->sendMessage("PhoneCall3");

        $target->sendLocalizedMessage("{PhoneCall1}" . $player->getProfile()->phoneNumber . ".."); 
        $target->sendMessage("PhoneCall2");
        $target->sendMessage("PhoneCall3");

        $player->getStatesMap()->phoneReq = null;
        $target->getStatesMap()->phoneSnd = null;

        $player->getStatesMap()->phoneRcv = $target;
        $target->getStatesMap()->phoneRcv = $player;
    }
    
    private function rejectCall(MineParkPlayer $player)
    {
        $player->getStatesMap()->phoneReq->sendMessage("PhoneCallRejected");

        $player->getStatesMap()->phoneReq->getStatesMap()->phoneSnd = null;
        $player->getStatesMap()->phoneReq = null;
    }

    private function endCall(MineParkPlayer $player)
    {
        $player->sendMessage("PhoneCallEnd");
        $player->getStatesMap()->phoneRcv->sendMessage("PhoneCallEnd");

        $player->getStatesMap()->phoneRcv->getStatesMap()->phoneRcv = null;
        $player->getStatesMap()->phoneRcv = null;
    }

    private function cancelRequest(MineParkPlayer $player)
    {
        $player->sendMessage("PhoneCancelRequest");

        $player->getStatesMap()->phoneSnd->getStatesMap()->phoneReq = null;
        $player->getStatesMap()->phoneSnd = null;
    }

    public function breakCall(MineParkPlayer $player)
    {
        $player->getStatesMap()->phoneRcv = null;
        $player->sendMessage("PhoneErrorNet");
    }

    private function checkForEmergencyNumber(MineParkPlayer $initializer, int $number) : bool
    {
        $organisationId = null;

        if($number === self::EMERGENCY_NUMBER_POLICE) {
            $organisationId = Organisations::SECURITY_WORK;
        } elseif($number === self::EMERGENCY_NUMBER_AMBULANCE) {
            $organisationId = Organisations::DOCTOR_WORK;
        }

        if(!isset($organisationId)) {
            return false;
        }

        $this->makeEmergencyCall($initializer, $organisationId);

        return true;
    }

    private function makeEmergencyCall(MineParkPlayer $player, int $organisationId)
    {
        $messages = $this->generateEmergencyMessages($player);

        foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer = MineParkPlayer::cast($onlinePlayer);

            if($onlinePlayer->getProfile()->organisation !== $organisationId) {
                continue;
            }

            foreach($messages as $message) {
                $onlinePlayer->sendLocalizedMessage($message);
            }
        }

        $player->sendMessage("PhoneEventCallHelp1");
        $player->sendMessage("PhoneEventCallHelp2");
        $player->sendMessage("PhoneEventCallHelp3");
        $player->sendMessage("PhoneEventCallHelp4");
    }

    private function generateEmergencyMessages(MineParkPlayer $player) : array
    {
        $nearPoints = $this->mapProvider->getNearPoints($player->asPosition(), 15);

        $messages = [];

        if(!isset($nearPoints[0])) {
            $messages[] = "{PhoneEvent1}";
        } else {
            $messages[] = "{PhoneEvent2}";
        }

        $messages[] = "{PhoneEvent3}" . $player->getProfile()->phoneNumber;
        $messages[] = "{PhoneEvent4}" . $player->getProfile()->fullName;
        $messages[] = "{PhoneEvent5}" . implode(", ", $player->property);

        if(!isset($nearPoints[0])) {
            $messages[] = "{PhoneEvent6}";
        } else {
            $messages[] = "{PhoneEvent7}" . $nearPoints[0];
        }

        return $messages;
    }
}
?>