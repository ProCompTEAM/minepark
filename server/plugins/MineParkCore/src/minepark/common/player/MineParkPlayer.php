<?php
namespace minepark\common\player;

use Exception;

use minepark\Core;
use pocketmine\Player;
use pocketmine\math\Vector3;
use minepark\models\dtos\UserDto;
use minepark\models\player\StatesMap;
use minepark\Providers;
use pocketmine\network\SourceInterface;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class MineParkPlayer extends Player
{	
	private StatesMap $statesMap;

	private UserDto $profile;

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
	
	public function sendWindowMessage($text, $title = "")
	{
		$data = [];
		
		$data["type"] = "form";
		$data["title"] = $title;
		$data["content"] = $text;
		$data["buttons"] = [];
		
		$pk = new ModalFormRequestPacket();
		$pk->formId = 999;
		$pk->formData = json_encode($data);
		$this->dataPacket($pk);
    }
    
	public function sendSound(string $soundName, Vector3 $vector3 = null, int $volume = 500, int $pitch = 1)
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

	public function isAdministrator() : bool
	{
		return $this->profile->administrator || $this->isOp();
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
?>