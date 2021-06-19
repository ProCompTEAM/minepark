<?php
namespace minepark\components\organisations;

use minepark\Tasks;
use minepark\Providers;
use pocketmine\block\Block;

use pocketmine\world\Position;
use minepark\defaults\TimeConstants;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;
use minepark\components\organisations\Organisations;
use minepark\providers\BankingProvider;
use minepark\providers\MapProvider;

class NoFire extends Component
{
    public ?Position $oldpoint;

    private MapProvider $mapProvider;

    private BankingProvider $bankingProvider;
    
    public function initialize()
    {
        Tasks::registerRepeatingAction(TimeConstants::NOFIRE_UPDATE_INTERVAL, [$this, "timeToFire"]);

        $this->oldpoint = null;

        $this->mapProvider = Providers::getMapProvider();

        $this->bankingProvider = Providers::getBankingProvider();
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
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
            $this->core->getChatter()->sendLocalMessage($player, "§8(§dв руках огнетушитель§8)", "§d : ", 10);
            
            if($this->clearPlace($player->getPosition(), 5)) {
                $this->bankingProvider->givePlayerMoney($player, 2000);
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

        foreach ($this->getServer()->getOnlinePlayers() as $player) {
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

        $points = $this->mapProvider->getNearPoints($noFires[0], 5000);

        if((count($points) > 0)) {
            $point = $points[mt_rand(0, count($points) - 1)];

            if($this->mapProvider->getPointGroup($point) < 3) {
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

            $cpos = $this->mapProvider->getPointPosition($point);

            $pos = new Position($cpos->getX() + $offsetX, $cpos->getY(), $cpos->getZ() + $offsetZ, $cpos->getWorld());
            
            if($pos->getWorld()->getBlock($pos)->getId() == 0) {
                $pos->getWorld()->setBlock($pos, Block::get(51), true, true);
                
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
            
        foreach($this->getServer()->getOnlinePlayers() as $p) {
            if($p->isOperator()) {
                $p->sendMessage("§7[§6!§7] Fire : На территории $fire_created начался пожар!");
            }
        }
            
        if($this->oldpoint != null) {
            $this->clearPlace($this->oldpoint, 20);
        }

        $this->oldpoint = $this->mapProvider->getPointPosition($fire_created);
    }

    private function tryToClearPlace(Position $pos, float $x, float $y, float $z) : bool
    {
        $newpos = new Position($x, $y, $z, $pos->getWorld());

        if($pos->getWorld()->getBlock($newpos)->getId() == 51) {
            $newpos->getWorld()->setBlock($newpos, Block::get(0), true, true);
            return true;
        }

        return false;
    }
}