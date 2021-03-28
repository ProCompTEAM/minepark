<?php
namespace minepark\components;

use minepark\Api;
use minepark\Providers;
use minepark\utils\CallbackTask;
use minepark\defaults\MapConstants;
use minepark\models\dtos\PasswordDto;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\TimeConstants;
use minepark\providers\data\UsersDataProvider;
use minepark\Tasks;

class Auth extends Component
{
    public const STATE_REGISTER = 0;
    public const STATE_NEED_AUTH = 1;
    public const STATE_AUTO = 2;

    public const WELCOME_MESSAGE_TIMEOUT = 2;

    private $ips = [];

    public function getAttributes() : array
    {
        return [
        ];
    }
    
    public function checkState(MineParkPlayer $player) : int
    {
        if(!$this->getDataProvider()->isUserPasswordExist($player->getName())) {
            return self::STATE_REGISTER;
        } else {
            if(isset($this->ips[$player->getName()]) and $this->ips[$player->getName()] == $player->getAddress()) {
                return self::STATE_AUTO;
            }
            else {
                return self::STATE_NEED_AUTH;
            }
        }
    }
    
    public function preLogin(MineParkPlayer $player)
    {
        $state = $this->checkState($player);

        $this->setMovement($player, false);

        switch($state) {
            case self::STATE_REGISTER:
                $player->getStatesMap()->bar = "AuthPasswordRegister"; 
            break;
            case self::STATE_NEED_AUTH:
                $player->getStatesMap()->bar = "AuthPasswordLogin"; 
            break;
            case self::STATE_AUTO:
                $this->autoLogInUser($player);
            break;
            default:
                $player->getStatesMap()->bar = "AuthError"; 
            break;
        }
    }
    
    public function login(MineParkPlayer $player, string $password)
    {
        $state = $this->checkState($player);

        if($state == self::STATE_REGISTER) {
            if(strlen($password) < 6) {
                $player->sendMessage("AuthLen");
            } else {
                $this->registerUser($player, $password);
            }
        } elseif($state == self::STATE_NEED_AUTH) {
            if(strlen($password) < 6) {
                $player->kick("AuthLen");
            } elseif(md5($password) == $this->getDataProvider()->getUserPassword($player->getName())) {
                $this->logInUser($player);
            } else {
                $player->kick("AuthInvalid");
            }
        }
    }
    
    public function setMovement($player, bool $status)
    {
        $player->setImmobile(!$status);
    }

    public function sendWelcomeText(MineParkPlayer $player)
    {	
        $player->addTitle("WelcomeTitle1","WelcomeTitle2", 5);
        $this->sendWelcomeChatText($player);
    }
    
    public function sendWelcomeChatText(MineParkPlayer $player)
    {
        $player->sendMessage("WelcomeTextMessage1");
        $player->sendMessage("WelcomeTextMessage2");
        $player->sendMessage("WelcomeTextMessage3");
        $player->sendMessage("WelcomeTextMessage4");
        $player->sendMessage("WelcomeTextMessage5");
    }

    private function logInUser(MineParkPlayer $player)
    {
        $player->getStatesMap()->auth = true;
        $player->getStatesMap()->bar = null;

        $this->ips[$player->getName()] = $player->getAddress();
        
        if($this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_ARRESTED)) {
            Providers::getMapProvider()->teleportPoint($player, MapConstants::POINT_NAME_JAIL);
        } else {
            $this->sendWelcomeText($player);
        }
        
        $this->setMovement($player, true);
    }

    private function registerUser(MineParkPlayer $player, string $password)
    {
        $this->updatePassword($player, $password);
        $this->ips[$player->getName()] = $player->getAddress();

        $player->getStatesMap()->auth = true;
        $player->getStatesMap()->bar = null; 

        $this->sendWelcomeText($player);
        $player->sendLocalizedMessage("{AuthStart}" . $password);

        $this->setMovement($player, true);
    }

    private function updatePassword(MineParkPlayer $player, string $password) 
    {
        $passwordDto = new PasswordDto();
        $passwordDto->name = $player->getName();
        $passwordDto->password = md5($password);

        $this->getDataProvider()->setUserPassword($passwordDto);
    }

    private function autoLogInUser(MineParkPlayer $player)
    {
        $player->getStatesMap()->auth = true; 
        $player->getStatesMap()->bar = null;

        $this->setMovement($player, true);

        $timeoutTicks = TimeConstants::ONE_SECOND_TICKS * self::WELCOME_MESSAGE_TIMEOUT;
        Tasks::registerDelayedAction($timeoutTicks, [$this, "sendWelcomeText"], [$player]);
    }

    private function getDataProvider() : UsersDataProvider
    {
        return Providers::getUsersDataProvider();
    }
}
?>