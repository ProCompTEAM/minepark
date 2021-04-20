<?php
namespace minepark\components\map;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use LogicException;
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
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class FloatingTexts extends Component
{
    private const FLOATING_TEXT_TAG = "FLOATING_TEXT";

    private const CHOICE_CREATE = 0;
    
    private const CHOICE_REMOVE = 1;

    private FloatingTextsDataProvider $floatingTextsDataProvider;

    private array $floatingTexts;

    public function initialize()
    {
        $this->floatingTextsDataProvider = Providers::getFloatingTextsDataProvider();

        $this->floatingTexts = $this->floatingTextsDataProvider->getAll();

        Events::registerEvent(EventList::PLAYER_JOIN_EVENT, [$this, "playerJoinEvent"]);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function playerJoinEvent(PlayerJoinEvent $event)
    {
        $this->initializeFloatingTexts($event->getPlayer());
    }

    public function getFloatingTexts() : array
    {
        return $this->floatingTexts;
    }

    public function getFloatingText(string $level, int $x, int $y, int $z) : ?FloatingTextDto
    {
        foreach($this->getFloatingTexts() as $floatingText) {
            if($floatingText->level === $level && $floatingText->x === $x && $floatingText->y === $y && $floatingText->z === $z) {
                return $floatingText;
            }
        }

        return null;
    }

    public function initializeMenu(MineParkPlayer $player)
    {
        $form = new SimpleForm([$this, "answerMenu"]);
        $form->setTitle("Управление летающими текстами");
        $form->addButton("Создать летающий текст");
        $form->addButton("Удалить летающий текст");
        $player->sendForm($form);
    }

    public function answerMenu(MineParkPlayer $player, ?int $choice = null)
    {
        if(!isset($choice)) {
            $player->sendMessage("Удачи Вам!");
            return;
        }

        switch($choice) {
            case self::CHOICE_CREATE:
                $this->initializeCreateForm($player);
            break;

            case self::CHOICE_REMOVE:
                $this->tryRemove($player);
            break;
        }
    }

    private function initializeCreateForm(MineParkPlayer $player)
    {
        $form = new CustomForm([$this, "answerCreateForm"]);
        $form->setTitle("Создать летающий текст");
        $form->addInput("Содержимое");
        $player->sendForm($form);
    }

    public function answerCreateForm(MineParkPlayer $player, ?array $data = null)
    {
        if(!isset($data)) {
            $player->sendMessage("Вы отменили создание.");
            return;
        }

        $input = $data[0];

        if(strlen($input) < 1) {
            $player->sendMessage("Как минимум в летающем тексте должен быть один символ.");
            return;
        }

        $block = $this->getBlockUnderPosition($player->asPosition());

        if(!isset($block)) {
            $player->sendMessage("Извините, но Вы не стоите на блоке.");
            return;
        }

        $this->save($input, $block->getLevel()->getName(), $block->getX(), $block->getY(), $block->getZ());

        $player->sendMessage("Летающий текст успешно создан!");
    }

    private function tryRemove(MineParkPlayer $player)
    {
        $block = $this->getBlockUnderPosition($player->asPosition());

        if(!isset($block)) {
            $player->sendMessage("Извините, но Вы не стоите на блоке.");
            return;
        }

        $status = $this->remove($block->asPosition());

        if(!$status) {
            $player->sendMessage("Удалить не удалось.");
        } else {
            $player->sendMessage("Текст успешно удалился!");
        }
    }

    public function save(string $text, string $level, int $x, int $y, int $z)
    {
        $dto = new LocalFloatingTextDto;

        $dto->text = $text;
        $dto->level = $level;
        $dto->x = $x;
        $dto->y = $y;
        $dto->z = $z;

        $dto = $this->floatingTextsDataProvider->save($dto);

        $this->addToMemory($dto);

        $this->showFloatingText($dto);
    }

    public function remove(Position $position) : bool
    {
        $floatingText = $this->getFloatingText($position->getLevel()->getName(), $position->getX(), $position->getY(), $position->getZ());

        if(!isset($floatingText)) {
            return false;
        }

        $positionDto = $this->getPositionDto($position);

        $status = $this->floatingTextsDataProvider->remove($positionDto);

        if(!$status) {
            throw new LogicException("FloatingText is on PHP, but not on MDC");
        }
        
        $this->removeFromMemory($floatingText);

        $this->hideFloatingText($floatingText);

        return true;
    }

    private function getBlockUnderPosition(Position $position) : ?Block
    {
        $x = $position->getFloorX();
        $y = $position->getY();
        $z = $position->getFloorZ();

        $block = null;

        if(is_int($y)) {
            $block = $position->getLevel()->getBlockAt($x, $y - 1, $z, false, false);
        } else {
            $block = $position->getLevel()->getBlockAt($x, $y - 0.5, $z, false, false);
        }

        if($block->getId() !== Block::AIR) {
            return $block;
        }

        return null;
    }

    private function initializeFloatingTexts(MineParkPlayer $player)
    {
        foreach($this->getFloatingTexts() as $floatingText) {
            $level = $this->getServer()->getLevelByName($floatingText->level);

            if(!isset($level) or $player->getLevel()->getName() !== $floatingText->level) {
                return;
            }

            $position = new Position($floatingText->x, $floatingText->y + 0.6, $floatingText->z, $level);

            $player->setFloatingText($position, $floatingText->text, self::FLOATING_TEXT_TAG);
        }

        $player->showFloatingTexts();
    }

    private function showFloatingText(FloatingTextDto $dto)
    {
        $level = $this->getServer()->getLevelByName($dto->level);

        if(!isset($level)) {
            return;
        }

        $position = new Position($dto->x, $dto->y + 0.6, $dto->z, $level);

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);

            if($player->getLevel()->getName() !== $dto->level) {
                continue;
            }

            $floatingText = $player->getFloatingText($position);

            if(!isset($floatingText)) {
                $player->setFloatingText($position, $dto->text, self::FLOATING_TEXT_TAG);
            } else {
                $floatingText->text = $dto->text;

                $player->updateFloatingText($floatingText);
            }

            $player->showFloatingTexts();
        }
    }

    private function hideFloatingText(FloatingTextDto $dto)
    {
        $level = $this->getServer()->getLevelByName($dto->level);

        if(!isset($level)) {
            return;
        }

        $position = new Position($dto->x, $dto->y + 0.6, $dto->z, $level);

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $this->hideFloatingTextForPlayer($player, $position);
        }
    }

    private function hideFloatingTextForPlayer(MineParkPlayer $player, Position $position)
    {
        $player->unsetFloatingText($player->getFloatingText($position));

        $player->showFloatingTexts();
    }

    private function addToMemory(FloatingTextDto $dto)
    {
        $index = $this->removeFromMemory($dto);

        if(isset($index)) {
            $this->floatingTexts[$index] = $dto;
        } else {
            array_push($this->floatingTexts, $dto);
        }
    }

    private function removeFromMemory(FloatingTextDto $dto) : ?int
    {
        $floatingTextIndex = array_search($dto, $this->floatingTexts);

        if(!isset($this->floatingTexts[$floatingTextIndex])) {
            return null;
        }

        unset($this->floatingTexts[$floatingTextIndex]);

        return $floatingTextIndex;
    }

    private function getPositionDto(Position $position) : PositionDto
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