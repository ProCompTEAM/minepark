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
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\world\Position;
use pocketmine\world\World;

class FloatingTexts extends Component
{
    private const FLOATING_TEXT_TAG = "FLOATING_TEXT";

    private const CREATE_CHOICE = 0;

    private const REMOVE_CHOICE = 1;

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
        $this->initializeAllFloatingTexts($event->getPlayer());
    }

    public function getFloatingTexts()
    {
        return $this->floatingTexts;
    }

    public function getFloatingText(string $level, float $x, float $y, float $z) : ?FloatingTextDto
    {
        $x = floor($x);
        $y = floor($y);
        $z = floor($z);

        foreach($this->getFloatingTexts() as $floatingText) {
            $textX = floor($floatingText->x);
            $textY = floor($floatingText->y);
            $textZ = floor($floatingText->z);

            if($textX === $x and $textY === $y and $textZ === $z and $floatingText->level === $level) {
                return $floatingText;
            }
        }

        return null;
    }

    public function save(string $text, Position $position)
    {
        $levelName = $position->getWorld()->getDisplayName();

        $floatingText = $this->getFloatingText($levelName, $position->getX(), $position->getY(), $position->getZ());

        if(is_null($floatingText)) {
            $dto = $this->createLocalFloatingTextDto($text, $position);
        } else {
            $dto = $this->getLocalFloatingTextDto($floatingText);
            $dto->text = $text;

            $this->removeFromMemory($floatingText);
        }

        $floatingText = $this->floatingTextsDataProvider->save($dto);

        $this->addToMemory($floatingText);

        $this->showFloatingText($floatingText);
    }

    public function remove(Position $position) : bool
    {
        $levelName = $position->getWorld()->getDisplayName();

        $floatingText = $this->getFloatingText($levelName, $position->getX(), $position->getY(), $position->getZ());

        if(is_null($floatingText)) {
            return false;
        }

        $dto = $this->getPositionDto($floatingText->level, $floatingText->x, $floatingText->y, $floatingText->z);

        $this->floatingTextsDataProvider->remove($dto);

        $this->hideFloatingText($floatingText);
        $this->removeFromMemory($floatingText);

        return true;
    }

    public function initializeMenu(MineParkPlayer $player)
    {
        $form = new SimpleForm([$this, "answerMenu"]);
        $form->setTitle("§bУправление текстами");
        $form->addButton("§eДобавить летающий текст");
        $form->addButton("§eУдалить летающий текст");
        $player->sendForm($form);
    }

    public function answerMenu(MineParkPlayer $player, ?int $data = null)
    {
        if(!isset($data)) {
            $player->sendMessage("§bУдачи!");
            return;
        }

        switch($data) {
            case self::CREATE_CHOICE:
                $this->initializeCreateForm($player);
            break;

            case self::REMOVE_CHOICE:
                $this->tryToRemove($player);
            break;

            default:
                throw new LogicException("Form answer out of choices");
        }
    }

    private function initializeCreateForm(MineParkPlayer $player)
    {
        $form = new CustomForm([$this, "answerCreateForm"]);
        $form->setTitle("§bСоздать текст");
        $form->addInput("§eВпишите, пожалуйста, содержимое текста.");
        $player->sendForm($form);
    }

    public function answerCreateForm(MineParkPlayer $player, ?array $data = null)
    {
        if(!isset($data)) {
            $player->sendMessage("§bУдачи!");
            return;
        }

        $input = $data[0];

        if(strlen($input) === 0) {
            $player->sendMessage("§eВ содержимом должен быть хотя бы 1 символ.");
            return;
        }

        $this->save($input, $player->getPosition());

        $player->sendMessage("§eНадпись успешно создана!");
    }

    private function tryToRemove(MineParkPlayer $player)
    {
        $removedStatus = $this->remove($player->getPosition());

        if(!$removedStatus) {
            $player->sendMessage("§eУдаление надписи прошло неуспешно.");
            return;
        }

        $player->sendMessage("§eНадпись успешно удалена!");
    }

    private function getLocalFloatingTextDto(FloatingTextDto $floatingText) : LocalFloatingTextDto
    {
        $dto = new LocalFloatingTextDto;
        $dto->level = $floatingText->level;
        $dto->text = $floatingText->text;
        $dto->x = $floatingText->x;
        $dto->y = $floatingText->y;
        $dto->z = $floatingText->z;
        return $dto;
    }

    private function getPositionDto(string $level, float $x, float $y, float $z) : PositionDto
    {
        $dto = new PositionDto;
        $dto->level = $level;
        $dto->x = $x;
        $dto->y = $y;
        $dto->z = $z;
        return $dto;
    }

    private function createLocalFloatingTextDto(string $text, Position $position) : LocalFloatingTextDto
    {
        $dto = new LocalFloatingTextDto;
        $dto->text = $text;
        $dto->level = $position->getWorld()->getDisplayName();
        $dto->x = $position->getX();
        $dto->y = $position->getY();
        $dto->z = $position->getZ();
        return $dto;
    }

    private function addToMemory(FloatingTextDto $dto)
    {
        array_push($this->floatingTexts, $dto);
    }

    private function removeFromMemory(FloatingTextDto $dto)
    {
        $floatingTextIndex = array_search($dto, $this->floatingTexts);

        if(!isset($this->floatingTexts[$floatingTextIndex])) {
            return null;
        }

        unset($this->floatingTexts[$floatingTextIndex]);
    }

    private function initializeAllFloatingTexts(MineParkPlayer $player)
    {
        foreach($this->getFloatingTexts() as $floatingText) {
            $level = $this->getServer()->getWorldManager()->getWorldByName($floatingText->level);

            if(!isset($level)) {
                return;
            }

            $position = new Position($floatingText->x, $floatingText->y + 0.5, $floatingText->z, $level);

            $player->setFloatingText($position, $floatingText->text, self::FLOATING_TEXT_TAG);
        }

        $player->showFloatingTexts();
    }

    private function showFloatingText(FloatingTextDto $floatingText)
    {
        $level = $this->getServer()->getWorldManager()->getWorldByName($floatingText->level);

        if(!isset($level)) {
            return;
        }

        $position = new Position($floatingText->x, $floatingText->y + 0.5, $floatingText->z, $level);

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $this->showFloatingTextForPlayer($player, $floatingText->text, $level, $position);
        }
    }

    private function showFloatingTextForPlayer(MineParkPlayer $player, string $text, World $level, Position $position)
    {
        if($level != $player->getWorld()) {
            return;
        }

        $floatingText = $player->getFloatingText($position);

        if(!isset($floatingText)) {
            $player->setFloatingText($position, $text, self::FLOATING_TEXT_TAG);
        } else {
            $floatingText->text = $text;

            $player->updateFloatingText($floatingText);
        }

        $player->showFloatingTexts();
    }

    private function hideFloatingText(FloatingTextDto $dto)
    {
        $level = $this->getServer()->getWorldManager()->getWorldByName($dto->level);

        if(!isset($level)) {
            return;
        }

        $position = new Position($dto->x, $dto->y + 0.5, $dto->z, $level);

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $this->hideFloatingTextForPlayer($player, $position);
        }
    }

    private function hideFloatingTextForPlayer(MineParkPlayer $player, Position $position)
    {
        $player->unsetFloatingText($player->getFloatingText($position));

        $player->showFloatingTexts();
    }
}