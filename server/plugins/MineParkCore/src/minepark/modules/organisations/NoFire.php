<?php
namespace minepark\modules\organisations;

use minepark\common\player\MineParkPlayer;
use minepark\Core;
use minepark\Providers;

use pocketmine\block\Block;
use pocketmine\level\Position;
use minepark\utils\CallbackTask;
use minepark\modules\organisations\Organisations;

class NoFire
{
	public $oldpoint;
	
	public function __construct()
	{
		$this->getCore()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "timeToFire"]), 20 * 60 * 3);
		$this->oldpoint = null;
    }
    
    protected function getCore() : Core
    {
        return Core::getActive();
    }
	
	public function timeToFire()
	{
		$players = $this->getAllNoFire();
		
		$fire_created = $this->handleCreateFire($players);
		
		if($fire_created != null) {
			$this->fireWarning($players, $fire_created);
		}
    }
    
    public static function emptyArray(array $array) : bool
    {
        return count($array) <= 0;
    }

	public function clean($player)
	{
		if($player->getProfile()->organisation == Organisations::EMERGENCY_WORK) {
            $this->core->getChatter()->send($player, "§8(§dв руках огнетушитель§8)", "§d : ", 10);
            
			if($this->clearPlace($player->getPosition(), 5)) {
				Providers::getBankingProvider()->givePlayerMoney($player, 2000);
				$player->sendMessage("§c[§e➪§c] §aОчаг потушен! (+2000)");
			}
		}
	}
	
	public function clearPlace(Position $pos, $rad) : bool
	{
        $status = false;

        $y = $pos->getY();

		for($x = ($pos->getX() - $rad); $x < ($pos->getX() + $rad); $x++) {
			for($z = ($pos->getZ() - $rad); $z < ($pos->getZ() + $rad); $z++) {
                $statusz = $this->tryToClearPlace($pos, $x, $y, $z);
    
                if ($statusz) {
                    $status = true;
                }
			}
        }

		return $status;
    }
    
    private function getAllNoFire() : array
    {
        $list = [];

        foreach ($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);
            if ($player->getProfile()->organisation == Organisations::EMERGENCY_WORK) {
                $list[] = $player;
            }
        }

        return $list;
    }

    private function handleCreateFire(array $noFires) : ?Position
    {
        if (self::emptyArray($noFires)) {
            return null;
        }

        $points = $this->getCore()->getMapper()->getNearPoints($noFires[0], 5000);

		if((count($points) > 0)) {
            $point = $points[mt_rand(0, count($points) - 1)];

			if($this->getCore()->getMapper()->getPointGroup($point) < 3) {
				return $this->makeRandomFire($point);
			}
		}
    }

    private function makeRandomFire(string $point) : ?string
    {
        $fire_created = null;

        for($i = 0; $i < 5; $i++) {
            $offsetX = mt_rand(0, 5);
            $offsetZ = mt_rand(0, 5);

            $cpos = $this->getCore()->getMapper()->getPointPosition($point);

            $pos = new Position($cpos->getX() + $offsetX, $cpos->getY(), $cpos->getZ() + $offsetZ, $cpos->getLevel());
            
            if($pos->getLevel()->getBlock($pos)->getId() == 0) {
                $pos->getLevel()->setBlock($pos, Block::get(51), true, true);
                
                if($fire_created == null) {
                    $fire_created = $point;
                }
            }
        }

        return $fire_created;
    }

    private function fireWarning(array $noFires, string $fire_created)
    {
        foreach($noFires as $p) {
			$p->sendMessage("§c[§e➪§c] §6!!! §e<=== §6ЭКСТРЕННЫЙ ВЫЗОВ (ОТПРАВЛЯЙТЕСЬ) §e===>");
			$p->sendMessage("§c[§e➪§c] §6!!! §cТРЕВОГА! §eВОЗГОРАНИЕ НА ТЕРРИТОРИИ  $fire_created !");
			$p->sendMessage("§c[§e➪§c] §6!!! §eНЕМЕДЛЕННО ВЫЕЗЖАЙТЕ: §7/gps §b $fire_created");
		}
			
		foreach($this->getCore()->getServer()->getOnlinePlayers() as $p) {
			if($p->isOp()) {
				$p->sendMessage("§7[§6!§7] Fire : На территории $fire_created начался пожар!");
			}
		}
			
		if($this->oldpoint != null) {
            $this->clearPlace($this->oldpoint, 20);
        }

		$this->oldpoint = $this->getCore()->getMapper()->getPointPosition($fire_created);
    }

    private function tryToClearPlace(Position $pos, float $x, float $y, float $z) : bool
    {
        $newpos = new Position($x, $y, $z, $pos->getLevel());

		if($pos->getLevel()->getBlock($newpos)->getId() == 51) {
			$newpos->getLevel()->setBlock($newpos, Block::get(0), true, true);
			return true;
        }

        return false;
    }
}
?>