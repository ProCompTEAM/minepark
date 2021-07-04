<?php
namespace minepark\components;

use minepark\Events;
use pocketmine\entity\AttributeFactory;
use pocketmine\entity\Entity;
use minepark\defaults\EventList;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;
use minepark\defaults\TimeConstants;
use minepark\models\entities\BossBarEntity;
use minepark\models\player\BossBarSession;
use minepark\Tasks;
use pocketmine\entity\Location;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataTypes;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute;
use pocketmine\entity\Attribute as AttributeIds;

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
            ComponentAttributes::SHARED,
            ComponentAttributes::STANDALONE
        ];
    }

    public function joinControl(PlayerJoinEvent $event)
    {
        $this->initializePlayerSession($event->getPlayer());

        $player = MineParkPlayer::cast($event->getPlayer());
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

        $packet = BossEventPacket::title($fakeEntityId, $title);

        $player->getNetworkSession()->sendDataPacket($packet);

        $player->getStatesMap()->bossBarSession->title = $title;
    }

    public function setPercentage(MineParkPlayer $player, int $fakeEntityId, int $percents)
    {
        if ($player->getStatesMap()->bossBarSession?->percents === $percents) {
            return;
        }

        $percentage = $percents / 100;

        $packet = BossEventPacket::healthPercent($fakeEntityId, $percentage);

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

        $attribute = new Attribute(AttributeIds::HEALTH, 0, 100, 30, 30);

        $packet->entityRuntimeId = $fakeEntityId;
        $packet->entries[] = $attribute;

        $player->getNetworkSession()->sendDataPacket($packet);
    }

    private function createBossEntity(MineParkPlayer $player, int $fakeEntityId) : int
    {
        $packet = new AddActorPacket;
        
        $packet->entityRuntimeId = $fakeEntityId;
        $packet->type = EntityIds::SLIME;
        $packet->metadata = $this->getHiddenEntityMetadata();
        $packet->position = $player->getPosition()->asVector3()->add(0, 10, 0);
        $packet->motion = null;

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
        $packet = BossEventPacket::show($fakeEntityId, $title, $percents);

        return $packet;
    }

    private function getHiddenEntityMetadata() : array
    {
        $properties = new EntityMetadataCollection;

        $properties->setGenericFlag(EntityMetadataFlags::SILENT, true);
        $properties->setGenericFlag(EntityMetadataFlags::INVISIBLE, true);
        $properties->setGenericFlag(EntityMetadataFlags::NO_AI, true);

        $properties->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
        $properties->setFloat(EntityMetadataProperties::SCALE, 0);
        $properties->setString(EntityMetadataProperties::NAMETAG, "");
        $properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0);
        $properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);

        return $properties->getAll();
    }
}