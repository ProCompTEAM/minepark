<?php
namespace minepark\components\organisations;

use minepark\Components;
use minepark\components\chat\Chat;
use minepark\models\dtos\MapPointDto;
use minepark\Tasks;
use minepark\Providers;

use minepark\utils\ArraysUtility;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use minepark\defaults\TimeConstants;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;
use minepark\defaults\OrganisationConstants;
use minepark\providers\BankingProvider;
use minepark\providers\MapProvider;
use pocketmine\world\World;

class NoFire extends Component
{
    private const MINIMAL_X_FIRE_SPAWN_RADIUS = -5;
    private const MINIMAL_Y_FIRE_SPAWN_RADIUS = 0;
    private const MINIMAL_Z_FIRE_SPAWN_RADIUS = -5;

    private const MAXIMAL_X_FIRE_SPAWN_RADIUS = 5;
    private const MAXIMAL_Y_FIRE_SPAWN_RADIUS = 5;
    private const MAXIMAL_Z_FIRE_SPAWN_RADIUS = 5;

    public ?Position $currentPoint;

    private MapProvider $mapProvider;

    private BankingProvider $bankingProvider;

    private Chat $chat;
    
    public function initialize()
    {
        // TODO: Fix fire creating in #511
        Tasks::registerRepeatingAction(TimeConstants::NOFIRE_UPDATE_INTERVAL, [$this, "createFire"]);

        $this->currentPoint = null;

        $this->mapProvider = Providers::getMapProvider();

        $this->bankingProvider = Providers::getBankingProvider();

        $this->chat = Components::getComponent(Chat::class);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }

    /**
     * Fire cleaning
     */

    public function putOutFire(MineParkPlayer $player)
    {
        $this->chat->sendLocalMessage($player, "{FireExtinguisher}", "§d : ", 10);
            
        if($this->tryToPutOutFire($player->getPosition(), 5)) {
            $this->bankingProvider->givePlayerMoney($player, 2000);
            $player->sendMessage("FireExtinguisherSucces");
        }
    }
    
    public function tryToPutOutFire(Position $position, int $radius) : bool
    {
        $result = false;

        $y = $position->getY();

        for($x = ($position->getX() - $radius); $x < ($position->getX() + $radius); $x++) {
            for($z = ($position->getZ() - $radius); $z < ($position->getZ() + $radius); $z++) {
                $fireCleanStatus = $this->tryToClearPlace($position->getWorld(), $x, $y, $z);
    
                if($fireCleanStatus) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    private function tryToClearPlace(World $world, float $x, float $y, float $z) : bool
    {
        $newPosition = new Position($x, $y, $z, $world);

        if($world->getBlock($newPosition)->getId() === BlockLegacyIds::FIRE) {
            $world->setBlock($newPosition, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0));
            return true;
        }

        return false;
    }

    /**
     * Fire creating
     */

    public function createFire()
    {
        $fireFighters = $this->getAllFireFighters();

        $firePoint = $this->tryToLightFire($fireFighters);

        if(!is_null($firePoint)) {
            $this->fireWarning($fireFighters, $firePoint);
        }
    }

    private function tryToLightFire(array $fireFighters) : ?string
    {
        if(ArraysUtility::isArrayEmpty($fireFighters)) {
            return null;
        }

        $fireFighter = $fireFighters[0];

        $point = $this->getPointForFire($fireFighter->getPosition(), 5000, 3);

        if(is_null($point)) {
            return null;
        }

        $pointPosition = new Position($point->x, $point->y, $point->z, $fireFighter->getWorld());

        $this->makeRandomFire($pointPosition);

        if(!is_null($this->currentPoint)) {
            $this->tryToPutOutFire($this->currentPoint, 5);
        }

        $this->currentPoint = $pointPosition;

        return $point->name;
    }

    private function getPointForFire(Position $position, int $distance, int $maximalGroup) : ?MapPointDto
    {
        $points = $this->mapProvider->getNearPoints($position, $distance, false);

        foreach($points as $point) {
            if($point->groupId < $maximalGroup) {
                return $point;
            }
        }

        return null;
    }

    private function makeRandomFire(Position $pointPosition) : bool
    {
        $world = $pointPosition->getWorld();

        for($x = self::MINIMAL_X_FIRE_SPAWN_RADIUS; $x <= self::MAXIMAL_X_FIRE_SPAWN_RADIUS; $x++) {
            for($z = self::MINIMAL_Z_FIRE_SPAWN_RADIUS; $z <= self::MAXIMAL_Z_FIRE_SPAWN_RADIUS; $z++) {
                for($y = self::MINIMAL_Y_FIRE_SPAWN_RADIUS; $y <= self::MAXIMAL_Y_FIRE_SPAWN_RADIUS; $y++) {
                    $pointVector = $pointPosition->add($x, $y, $z);

                    if($this->checkForFireAvailability($pointVector, $world)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function checkForFireAvailability(Vector3 $vector, World $world) : bool
    {
        $block = $world->getBlock($vector);

        if($block->getId() !== BlockLegacyIds::AIR) {
            return false;
        }

        $bottomBlock = $block->getSide(Facing::DOWN);

        if(!$bottomBlock->isSolid()) {
            return false;
        }

        $world->setBlock($vector, BlockFactory::getInstance()->get(BlockLegacyIds::FIRE, 0));

        return true;
    }

    private function fireWarning(array $fireFighters, string $firePoint)
    {
        foreach($fireFighters as $fireFighter) {
            $fireFighter->sendMessage("FireCall1");
            $fireFighter->sendMessage("FireCall2");
            $fireFighter->sendMessage("FireCall3");
        }
            
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);
            if($player->isOperator()) {
                $player->sendMessage("FireCallOperator");
            }
        }
    }

    /**
     * Other
     */

    private function getAllFireFighters() : array
    {
        $list = [];

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);
            if($player->getSettings()->organisation === OrganisationConstants::EMERGENCY_WORK) {
                array_push($list, $player);
            }
        }

        return $list;
    }
}