<?php
namespace minepark\components;

use minepark\Events;
use pocketmine\entity\Entity;
use minepark\defaults\EventList;
use pocketmine\entity\Attribute;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\models\player\BossBarSession;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;

class BossBar extends Component
{
    private const DEFAULT_TITLE = "MinePark";
    private const DEFAULT_PERCENTS = 100;

    public function initialize()
    {
        Events::registerEvent(EventList::PLAYER_JOIN_EVENT, [$this, "joinControl"]);
    }

    public function getAttributes(): array
    {
        return [
        ];
    }

    public function joinControl(PlayerJoinEvent $event)
    {
        $this->initializePlayerSession($event->getPlayer());
    }

    public function initializePlayerSession(MineParkPlayer $player) : bool
    {
        if (isset($player->getStatesMap()->bossBarSession)) {
            return false;
        }

        $fakeEntityId = Entity::$entityCount++;

        $this->createBossEntity($player, $fakeEntityId);

        $player->getStatesMap()->bossBarSession = $this->generateSession($fakeEntityId);

        return true;
    }

    public function setBossBar(MineParkPlayer $player, ?string $title = null, ?int $percents = null)
    {
        $session = $player->getStatesMap()->bossBarSession;

        if (!isset($title)) {
            $title = $session->title ?? self::DEFAULT_TITLE;
        }

        if (!isset($percents)) {
            $percents = $session->percents ?? self::DEFAULT_PERCENTS;
        }

        if (!$session->loaded) {
            $session->loaded = true;

            $player->dataPacket($this->getBossEventPacket($session->fakeEntityId, $title, $percents));
        } else {
            $this->setTitle($player, $session->fakeEntityId, $title);
            $this->setPercentage($player, $session->fakeEntityId, $percents);
        }

        $session->title = $title;
        $session->percents = $percents;

        $player->getStatesMap()->bossBarSession = $session;
    }

    public function setTitle(MineParkPlayer $player, int $fakeEntityId, string $title)
    {
        if ($player->getStatesMap()->bossBarSession?->title === $title) {
            return;
        }

        $packet = new BossEventPacket;

        $packet->eventType = BossEventPacket::TYPE_TITLE;
        $packet->bossEid = $fakeEntityId;
        $packet->title = $title;

        $player->dataPacket($packet);

        $player->getStatesMap()->bossBarSession->title = $title;
    }

    public function setPercentage(MineParkPlayer $player, int $fakeEntityId, int $percents)
    {
        if ($player->getStatesMap()->bossBarSession?->percents === $percents) {
            return;
        }

        $percentage = $percents / 100;

        $packet = new BossEventPacket;

        $packet->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;
        $packet->bossEid = $fakeEntityId;
        $packet->healthPercent = $percentage;

        $player->dataPacket($packet);

        $player->getStatesMap()->bossBarSession->percents = $percents;
    }

    public function hideBossBar(MineParkPlayer $player)
    {
        $packet = new BossEventPacket;

        $packet->bossEid = $player->getStatesMap()->bossBarSession->fakeEntityId;
        $packet->eventType = BossEventPacket::TYPE_HIDE;

        $player->dataPacket($packet);
    }

    private function initializeHealthAttribute(MineParkPlayer $player, int $fakeEntityId)
    {
        $packet = new UpdateAttributesPacket;

        $packet->entityRuntimeId = $fakeEntityId;
        $packet->entries[] = Attribute::getAttribute(Attribute::HEALTH)->setMinValue(0)->setMaxValue(100)->setDefaultValue(0);

        $player->dataPacket($packet);
    }

    private function createBossEntity(MineParkPlayer $player, int $fakeEntityId) : int
    {
        $packet = new AddActorPacket;
        
        $packet->entityRuntimeId = $fakeEntityId;
        $packet->type = AddActorPacket::LEGACY_ID_MAP_BC[Entity::SLIME];
        $packet->metadata = $this->getHiddenEntityMetadata();
        $packet->position = $player->asVector3()->add(0, 10, 0);

        $player->dataPacket($packet);

        $this->initializeHealthAttribute($player, $fakeEntityId);

        return $fakeEntityId;
    }

    private function generateSession(int $fakeEntityId) : BossBarSession
    {
        $session = new BossBarSession;

        $session->title = null;
        $session->percents = null;
        $session->fakeEntityId = $fakeEntityId;
        $session->loaded = false;

        return $session;
    }

    private function getBossEventPacket(int $fakeEntityId, string $title, int $percents) : BossEventPacket
    {
        $packet = new BossEventPacket;

        $packet->bossEid = $fakeEntityId;
        $packet->eventType = BossEventPacket::TYPE_SHOW;
        $packet->title = $title;
        $packet->healthPercent = $percents / 100;
        $packet->unknownShort = 0;
        $packet->color = 0;
        $packet->overlay = 0;
        $packet->playerEid = 0;

        return $packet;
    }

    private function getHiddenEntityMetadata() : array
    {
        return [
            Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1],
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI], 
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, ""], 
            Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], 
            Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]
        ];
    }
}
?>