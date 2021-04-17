<?php
namespace minepark\components\map;

use jojoe77777\FormAPI\CustomForm;
use minepark\common\player\MineParkPlayer;
use minepark\components\base\Component;
use minepark\defaults\ComponentAttributes;
use minepark\defaults\EventList;
use minepark\Events;
use minepark\models\dtos\FloatingTextDto;
use minepark\models\dtos\LocalFloatingTextDto;
use minepark\models\dtos\PositionDto;
use minepark\Providers;
use minepark\providers\data\FloatingTextsDataProvider;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class FloatingTexts extends Component
{
    private FloatingTextsDataProvider $floatingTextsDataProvider;

    private $floatingTexts;

    private $removeStatePlayers;

    private $createStatePlayers;

    private $chosenBlocks;

    public function initialize()
    {
        $this->floatingTextsDataProvider = Providers::getFloatingTextsDataProvider();

        $this->floatingTexts = $this->floatingTextsDataProvider->getAll();

        $this->removeStatePlayers = [];
        $this->createStatePlayers = [];

        Events::registerEvent(EventList::PLAYER_JOIN_EVENT, [$this, "playerJoinEvent"]);
        Events::registerEvent(EventList::PLAYER_QUIT_EVENT, [$this, "playerQuitEvent"]);
        Events::registerEvent(EventList::PLAYER_INTERACT_EVENT, [$this, "playerInteractEvent"]);
    }

    public function getAttributes(): array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function playerJoinEvent(PlayerJoinEvent $event)
    {
        $this->initializePlayerFloatingTexts($event->getPlayer());
    }

    public function playerQuitEvent(PlayerQuitEvent $event)
    {
        $this->setCreateState($event->getPlayer(), false);
        $this->setRemoveState($event->getPlayer(), false);
        $this->setChosenBlock($event->getPlayer(), null);
    }

    public function playerInteractEvent(PlayerInteractEvent $event)
    {
        if($event->getBlock()->getId() === Block::AIR) {
            return;
        }

        if($this->isCreateState($event->getPlayer())) {
            $this->openCreatingForm($event->getPlayer(), $event->getBlock());
        } else if($this->isRemoveState($event->getPlayer())) {
            $this->tryRemovingFloatingText($event->getPlayer(), $event->getBlock());
        }
    }

    public function save(string $text, Position $position)
    {
        $dto = $this->createLocalFloatingTextDto($text, $position);

        $floatingTextDto = $this->floatingTextsDataProvider->save($dto);

        $this->floatingTexts[] = $floatingTextDto;

        $this->displayFloatingText($dto);
    }

    public function remove(Position $position) : bool
    {
        $text = $this->getFloatingText($position->getLevel()->getName(), $position->getX(), $position->getY(), $position->getZ());

        if(!isset($text)) {
            return false;
        }

        $level = $this->getServer()->getLevelByName($text->level);
        $position = new Position($text->x, $text->y, $text->z, $level);

        $dto = $this->createPositionDto($position);

        $removedStatus = $this->floatingTextsDataProvider->remove($dto);

        if(!$removedStatus) {
            return false;
        } else {
            foreach($this->floatingTexts as $id => $floatingText) {
                if($floatingText->level !== $dto->level) {
                    continue;
                }

                if($floatingText->x === $dto->x and $floatingText->y === $dto->y and $floatingText->z === $dto->z) {
                    unset($this->floatingTexts[$id]);
                    break;
                }
            }
            $this->hideFloatingText($position);
            return true;
        }
    }

    public function getFloatingTexts() : array
    {
        return $this->floatingTexts;
    }

    public function getFloatingText(string $level, float $x, float $y, float $z) : ?FloatingTextDto
    {
        foreach($this->getFloatingTexts() as $floatingText) {
            if($floatingText->level !== $level) {
                continue;
            }

            if($floatingText->x === $x and $floatingText->y === $y and $floatingText->z === $z) {
                return $floatingText;
            }
        }

        return null;
    }

    public function isCreateState(MineParkPlayer $player) : bool
    {
        return in_array($player->getName(), $this->createStatePlayers);
    }

    public function isRemoveState(MineParkPlayer $player) : bool
    {
        return in_array($player->getName(), $this->removeStatePlayers);
    }

    public function setCreateState(MineParkPlayer $player, bool $status)
    {
        if(!$status and $this->isCreateState($player)) {
            unset($this->createStatePlayers[array_search($player->getName(), $this->createStatePlayers)]);
        } else if($status and !$this->isCreateState($player)) {
            $this->createStatePlayers[] = $player->getName();
        }
    }

    public function setRemoveState(MineParkPlayer $player, bool $status)
    {
        if(!$status and $this->isRemoveState($player)) {
            unset($this->removeStatePlayers[array_search($player->getName(), $this->removeStatePlayers)]);
        } else if($status and !$this->isRemoveState($player)) {
            $this->removeStatePlayers[] = $player->getName();
        }
    }

    public function getChosenBlock(MineParkPlayer $player) : ?Position
    {
        return isset($this->chosenBlocks[$player->getName()]) ? $this->chosenBlocks[$player->getName()] : null;
    }

    public function setChosenBlock(MineParkPlayer $player, ?Position $blockPosition = null)
    {
        if(!isset($blockPosition)) {
            unset($this->chosenBlocks[$player->getName()]);
        } else {
            $this->chosenBlocks[$player->getName()] = $blockPosition;
        }
    }

    public function openCreatingForm(MineParkPlayer $player, Block $block)
    {
        $this->setCreateState($player, false);

        $this->setChosenBlock($player, $block);

        $form = new CustomForm([$this, "answerCreatingForm"]);

        $form->setTitle("Создать летающую надпись");

        $form->addInput("Введите текст летающей надписи");

        $player->sendForm($form);
    }

    public function answerCreatingForm(MineParkPlayer $player, ?array $data = null)
    {
        if(!isset($data)) {
            $this->setChosenBlock($player, null);
            return;
        }

        $input = $data[0];

        if(strlen($input) <= 2) {
            $player->sendMessage("В одном летающем тексте должно быть минимум 2 символа.");
            return;
        }

        $chosenBlock = $this->getChosenBlock($player);

        if(!isset($chosenBlock)) {
            $player->sendMessage("Возможно, возникла какая-то проблема. Попробуйте снова.");
            return;
        }

        $this->save($input, $chosenBlock);

        $this->setCreateState($player, false);

        $player->sendMessage("Летающий текст создан");
    }

    private function tryRemovingFloatingText(MineParkPlayer $player, Position $position)
    {
        $removedStatus = $this->remove($position);

        if($removedStatus) {
            $player->sendMessage("Надпись успешно убрана!");
        } else {
            $player->sendMessage("На блоке нет надписи!");
            return;
        }

        $player->showFloatingTexts();

        $this->setRemoveState($player, false);
    }

    private function initializePlayerFloatingTexts(MineParkPlayer $player)
    {
        foreach($this->floatingTexts as $floatingText) {
            $level = $this->getServer()->getLevelByName($floatingText->level);

            if(!isset($level)) {
                continue;
            }

            if($player->getLevel() != $level) {
                continue;
            }

            $position = new Position($floatingText->x, $floatingText->y + 0.6, $floatingText->z, $level);

            $player->setFloatingText($position, $floatingText->text, "");
        }

        $player->showFloatingTexts();
    }

    private function displayFloatingText(LocalFloatingTextDto $floatingTextDto)
    {
        $level = $this->getServer()->getLevelByName($floatingTextDto->level);

        if(!isset($level)) {
            return;
        }

        $position = new Position($floatingTextDto->x, $floatingTextDto->y + 0.6, $floatingTextDto->z, $level);

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);

            if($player->getLevel() != $level) {
                continue;
            }

            $floatingText = $player->getFloatingText($position);

            if(!isset($floatingText)) {
                $player->setFloatingText($position, $floatingTextDto->text, "");
            } else {
                $floatingText->text = $floatingTextDto->text;
                $player->updateFloatingText($floatingText);
            }

            $player->showFloatingTexts();
        }
    }

    private function hideFloatingText(Position $position)
    {
        $position->y = $position->y + 0.6;

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);

            if($player->getLevel()->getName() !== $position->getLevel()->getName()) {
                continue;
            }

            $player->unsetFloatingText($player->getFloatingText($position));
        }
    }

    private function createLocalFloatingTextDto(string $text, Position $position) : LocalFloatingTextDto
    {
        $dto = new LocalFloatingTextDto;
        $dto->text = $text;
        $dto->level = $position->getLevel()->getName();
        $dto->x = $position->getX();
        $dto->y = $position->getY();
        $dto->z = $position->getZ();
        return $dto;
    }

    private function createPositionDto(Position $position) : PositionDto
    {
        $dto = new PositionDto;
        $dto->level = $position->getLevel()->getName();
        $dto->x = $position->getX();
        $dto->y = $position->getY();
        $dto->z = $position->getZ();
        return $dto;
    }
}
?>