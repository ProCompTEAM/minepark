<?php
namespace minepark\components;

use minepark\Core;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use minepark\components\base\Component;

class WorldProtector extends Component
{
	private int $minimumX;

	private int $minimumZ;

	private int $maximumX;

	private int $maximumZ;

	private string $level;

	public function __construct()
	{
		$this->loadConfiguration();
	}

	public function getAttributes() : array
    {
        return [
        ];
    }

	public function isInRange(Position $position) : bool
	{
		return (
			$position->getLevel()->getName() == $this->getLevelName() and
			$position->getX() >= $this->getMinimumX() and
			$position->getZ() >= $this->getMinimumZ() and
			$position->getX() <= $this->getMaximumX() and
			$position->getZ() <= $this->getMaximumZ()
		);
	}

	private function loadConfiguration()
	{
		$file = $this->getCore()->getServer()->getDataPath() . "world-protector.yml";
		$defaultLevelName = $this->getCore()->getServer()->getDefaultLevel()->getName();

        $config = new Config($file, Config::YAML, [
			"Level" => $defaultLevelName,
            "MinimumX" => -128,
			"MinimumZ" => -128,
			"MaximumX" => 128,
			"MaximumZ" => 128,
		]);
		
		$this->level = $config->get("Level");
		$this->minimumX = $config->get("MinimumX");
		$this->minimumZ = $config->get("MinimumZ");
		$this->maximumX = $config->get("MaximumX");
		$this->maximumZ = $config->get("MaximumZ");
	}
    
    private function getMinimumX() : int
	{
		return $this->minimumX;
	}

	private function getMinimumZ() : int
	{
		return $this->minimumZ;
	}

	private function getMaximumX() : int
	{
		return $this->maximumX;
	}

	private function getMaximumZ() : int
	{
		return $this->maximumZ;
	}

	private function getLevelName() : string
	{
		return $this->level;
	}
}
?>