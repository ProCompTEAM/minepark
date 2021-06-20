<?php
namespace minepark\components;

use minepark\Events;
use pocketmine\entity\AttributeFactory;
use pocketmine\entity\Entity;
use minepark\defaults\EventList;
use pocketmine\entity\Attribute;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\models\player\BossBarSession;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataTypes;
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

        $fakeEntityId = Entity::nextRuntimeId();

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

            $player->getNetworkSession()->sendDataPacket($this->getBossEventPacket($session->fakeEntityId, $title, $percents));
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

        $player->getNetworkSession()->sendDataPacket($packet);

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

        $player->getNetworkSession()->sendDataPacket($packet);

        $player->getStatesMap()->bossBarSession->percents = $percents;
    }

    public function hideBossBar(MineParkPlayer $player)
    {
        $packet = new BossEventPacket;

        $packet->bossEid = $player->getStatesMap()->bossBarSession->fakeEntityId;
        $packet->eventType = BossEventPacket::TYPE_HIDE;

        $player->getNetworkSession()->sendDataPacket($packet);
    }

    private function initializeHealthAttribute(MineParkPlayer $player, int $fakeEntityId)
    {
        $packet = new UpdateAttributesPacket;

        $packet->entityRuntimeId = $fakeEntityId;
        $packet->entries[] = AttributeFactory::getInstance()->get(Attribute::HEALTH)->setMinValue(0)->setMaxValue(100)->setDefaultValue(0);

        $player->getNetworkSession()->sendDataPacket($packet);
    }

    private function createBossEntity(MineParkPlayer $player, int $fakeEntityId) : int
    {
        $packet = new AddActorPacket;
        
        $packet->entityRuntimeId = $fakeEntityId;
        $packet->type = EntityIds::SLIME;
        $packet->metadata = $this->getHiddenEntityMetadata();
        $packet->position = $player->getPosition()->asVector3()->add(0, 10, 0);

        $player->getNetworkSession()->sendDataPacket($packet);

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
            EntityMetadataProperties::LEAD_HOLDER_EID => [EntityMetadataTypes::LONG, -1],
            EntityMetadataProperties::FLAGS => [EntityMetadataTypes::LONG, 0 ^ 1 << EntityMetadataFlags::SILENT ^ 1 << EntityMetadataFlags::INVISIBLE ^ 1 << EntityMetadataFlags::NO_AI],
            EntityMetadataProperties::SCALE => [EntityMetadataTypes::FLOAT, 0],
            EntityMetadataProperties::NAMETAG => [EntityMetadataTypes::STRING, ""],
            EntityMetadataProperties::BOUNDING_BOX_WIDTH => [EntityMetadataTypes::FLOAT, 0],
            EntityMetadataProperties::BOUNDING_BOX_HEIGHT => [EntityMetadataTypes::FLOAT, 0]
        ];
    }
}