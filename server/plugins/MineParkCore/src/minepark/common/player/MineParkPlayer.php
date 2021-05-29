<?php
namespace minepark\common\player;

use Exception;
use pocketmine\Player;
use minepark\Providers;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use minepark\models\dtos\UserDto;
use minepark\defaults\MapConstants;
use minepark\models\player\StatesMap;
use minepark\defaults\PlayerAttributes;
use pocketmine\network\SourceInterface;
use minepark\models\player\FloatingText;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class MineParkPlayer extends Player
{	
    private StatesMap $statesMap;

    private UserDto $profile;

    private array $floatingTexts = [];

    public static function cast(Player $player) : ?MineParkPlayer {
        if($player === null) {
            return null;
        } elseif($player instanceof MineParkPlayer) {
            return $player;
        } else {
            throw new Exception("Player isn't MineParkPlayer");
        }
    }

    public function __construct(SourceInterface $interface, string $ip, int $port)
    {
        parent::__construct($interface, $ip, $port);
    }
    
    public function __get($name) //rudiment
    {
        if(!isset($this->$name)) {
            return null;
        }
        
        return $this->$name;
    }
    
    public function __set($name, $value) //rudiment
    {
        $this->$name = $value;
    }

    /*
        Basic Getters
    */

    public function getProfile() : UserDto
    {
        return $this->profile;
    }

    public function setProfile(UserDto $profile)
    {
        $this->profile = $profile;
    }

    public function getStatesMap() : StatesMap
    {
        return $this->statesMap;
    }

    public function setStatesMap(StatesMap $map)
    {
        $this->statesMap = $map;
    }

    public function isAuthorized() : bool
    {
        return $this->statesMap->auth;
    }

    /*
        Permissions API
    */

    public function isAdministrator() : bool
    {
        return $this->profile->administrator or $this->isOp();
    }

    public function isVip() : bool
    {
        return $this->profile->vip;
    }

    public function isBuilder() : bool
    {
        return $this->profile->builder;
    }

    public function isRealtor() : bool
    {
        return $this->profile->realtor;
    }

    public function canBuild() : bool
    {
        return $this->isBuilder() or $this->isOp();
    }

    /*
        Common Player Functions
    */
    
    public function sendWindowMessage($text, $title = "")
    {
        $data = [];
        
        $data["type"] = "form";
        $data["title"] = $title;
        $data["content"] = $text;
        $data["buttons"] = [];
        
        $pk = new ModalFormRequestPacket();
        $pk->formId = 2147483647;
        $pk->formData = json_encode($data);
        $this->dataPacket($pk);
    }
    
    public function sendSound(string $soundName, Vector3 $vector3 = null, int $volume = 100, int $pitch = 1)
    {
        if($vector3 == null) {
            $vector3 = $this->getPosition()->add(0, 1, 0);
        }
        
        $pk = new PlaySoundPacket();
        $pk->soundName = $soundName;
        $pk->x = $vector3->getX();
        $pk->y = $vector3->getY();
        $pk->z = $vector3->getZ();
        $pk->volume = $volume;
        $pk->pitch = $pitch;
        
        $this->dataPacket($pk);
    }
    
    public function hasPermissions(array $permissions)
    {
        foreach($permissions as $permission) {
            if($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function sendCommand(string $command)
    {
        $ev = new PlayerCommandPreprocessEvent($this, $command);
        $ev->call();
    }

    /*
        Attributes
    */

    public function existsAttribute(string $key) : bool
    {
        return preg_match('/'.strtoupper($key).'/', $this->getProfile()->attributes);
    }

    public function changeAttribute(string $key, bool $status = true)
    {
        if(!$status) {
            $this->getProfile()->attributes = str_replace(strtoupper($key), '', $this->getProfile()->attributes);
        } elseif(!$this->existsAttribute(strtoupper($key))) {
            $this->getProfile()->attributes .= strtoupper($key);
        }

        Providers::getProfileProvider()->saveProfile($this);
    }

    /*
        Arest/Release API
    */

    public function arest()
    {
        Providers::getMapProvider()->teleportPoint($this, MapConstants::POINT_NAME_JAIL);
        $this->changeAttribute(PlayerAttributes::ARRESTED);
        $this->changeAttribute(PlayerAttributes::WANTED, false);
        $this->getStatesMap()->bar = "§6ВЫ АРЕСТОВАНЫ!";
    }

    public function release()
    {
        Providers::getMapProvider()->teleportPoint($this, MapConstants::POINT_NAME_ADIMINISTRATION);
        $this->changeAttribute(PlayerAttributes::ARRESTED, false);
        $this->changeAttribute(PlayerAttributes::WANTED, false);
        $this->getStatesMap()->bar = null;
    }

    /*
        Floating Texts API
    */

    public function setFloatingText(Position $position, string $text, string $tag = null) : FloatingText
    {
        $floatingText = new FloatingText;
        $floatingText->delivered = false;
        $floatingText->position = $position;
        $floatingText->text = $text;
        $floatingText->tag = $tag;
        $floatingText->particle = new FloatingTextParticle($position, $text, "");

        array_push($this->floatingTexts, $floatingText);

        return $floatingText;
    }

    public function getFloatingText(Position $position) : ?FloatingText
    {
        foreach($this->floatingTexts as $floatingText) {
            if($floatingText->position == $position) {
                return $floatingText;
            }
        }

        return null;
    }

    public function getFloatingTextsByTag(string $tag) : array
    {
        $floatingTexts = [];

        foreach($this->floatingTexts as $floatingText) {
            if($floatingText->tag === $tag) {
                array_push($floatingTexts, $floatingText);
            }
        }

        return $floatingTexts;
    }

    public function getFloatingTextsByText(string $text) : array
    {
        $floatingTexts = [];

        foreach($this->floatingTexts as $floatingText) {
            if($floatingText->text === $text) {
                array_push($floatingTexts, $floatingText);
            }
        }

        return $floatingTexts;
    }

    public function unsetFloatingText(FloatingText $floatingText)
    {
        $level = $floatingText->position->getLevel();

        $floatingText->particle->setInvisible(true);
        $level->addParticle($floatingText->particle, [$this]);
        
        foreach($this->floatingTexts as $key => $value) {
            if($floatingText == $value) {
                unset($this->floatingTexts[$key]);
                return;
            }
        }
    }

    public function updateFloatingText(FloatingText $floatingText)
    {
        $level = $floatingText->position->getLevel();
        $floatingText->particle->setText($floatingText->text);
        $level->addParticle($floatingText->particle, [$this]);
    }

    public function showFloatingTexts()
    {
        foreach($this->floatingTexts as $floatingText) {
            if(!$floatingText->delivered) {
                $level = $floatingText->position->getLevel();
                $level->addParticle($floatingText->particle, [$this]);
                $floatingText->delivered = true;
            }
        }
    }

    /*
        Localization
    */

    public function sendMessage($message)
    {
        parent::sendMessage(Providers::getLocalizationProvider()->take($this->locale, $message) ?? $message);
    }

    public function sendLocalizedMessage(string $message)
    {
        parent::sendMessage(Providers::getLocalizationProvider()->translateFrom($this->locale, $message));
    }

    public function sendTip(string $message)
    {
        parent::sendTip(Providers::getLocalizationProvider()->take($this->locale, $message) ?? $message);
    }

    public function sendLocalizedTip(string $message)
    {
        parent::sendTip(Providers::getLocalizationProvider()->translateFrom($this->locale, $message));
    }

    public function sendWhisper(string $sender, string $message)
    {
        parent::sendWhisper($sender, Providers::getLocalizationProvider()->take($this->locale, $message) ?? $message);
    }

    public function sendLocalizedWhisper(string $sender, string $message)
    {
        parent::sendWhisper($sender, Providers::getLocalizationProvider()->translateFrom($this->locale, $message));
    }

    public function sendPopup(string $message, string $subtitle = "")
    {
        parent::sendPopup(Providers::getLocalizationProvider()->take($this->locale, $message) ?? $message);
    }

    public function sendLocalizedPopup(string $message)
    {
        parent::sendPopup(Providers::getLocalizationProvider()->translateFrom($this->locale, $message));
    }

    public function addTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1)
    {
        $title = Providers::getLocalizationProvider()->take($this->locale, $title) ?? $title;
        $subtitle = Providers::getLocalizationProvider()->take($this->locale, $subtitle) ?? $subtitle;

        parent::sendTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
    }

    public function addLocalizedTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1)
    {
        $title = Providers::getLocalizationProvider()->translateFrom($this->locale, $title);
        $subtitle = Providers::getLocalizationProvider()->translateFrom($this->locale, $subtitle);

        parent::sendTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
    }

    public function kick(string $reason = "", bool $isAdmin = true, $quitMessage = null) : bool
    {
        if($reason === "") {
            return parent::kick();
        }

        $reason = Providers::getLocalizationProvider()->take($this->locale, $reason) ?? $reason;

        return parent::kick($reason, $isAdmin, $quitMessage);
    }
}