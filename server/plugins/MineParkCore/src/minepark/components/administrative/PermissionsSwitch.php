<?php
namespace minepark\components\administrative;

use minepark\Providers;
use minepark\components\base\Component;
use minepark\providers\ProfileProvider;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;
use pocketmine\form\Form;
use jojoe77777\FormAPI\CustomForm;

class PermissionsSwitch extends Component
{
    private const FORM_TOGGLE_OP = 0;

    private const FORM_TOGGLE_ADMIN = 1;

    private const FORM_TOGGLE_BUILDER = 2;

    private const FORM_TOGGLE_REALTOR = 3;

    private array $operators;

    private ProfileProvider $profileProvider;

    public function initialize()
    {
        $this->operators = [];

        $this->profileProvider = Providers::getProfileProvider();
    }

    public function getAttributes(): array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function getOperators() : array
    {
        return $this->operators;
    }

    public function isOperator(string $subjectName) : bool
    {
        return in_array($subjectName, $this->getOperators());
    }

    public function addOperator(string $subjectName)
    {
        $this->operators[] = $subjectName;
    }

    public function removeOperator(string $subjectName)
    {
        if($this->isOperator($subjectName)) {
            unset($this->operators[$subjectName]);
        }
    }

    public function generateForm(MineParkPlayer $player) : Form
    {
        $form = new CustomForm([$this, "answerForm"]);

        $profile = $player->getProfile();

        $form->setTitle("§eНастройка разрешений");
        $form->addToggle("§eOP", $player->isOperator());
        $form->addToggle("§eАдминистратор", $profile->administrator);
        $form->addToggle("§eСтроитель", $profile->builder);
        $form->addToggle("§eРиэлтор", $profile->realtor);

        return $form;
    }

    public function answerForm(MineParkPlayer $player, ?array $inputData = null)
    {
        if(!isset($inputData)) {
            return;
        }

        $toggleOp = $inputData[self::FORM_TOGGLE_OP];
        $toggleAdmin = $inputData[self::FORM_TOGGLE_ADMIN];
        $toggleBuilder = $inputData[self::FORM_TOGGLE_BUILDER];
        $toggleRealtor = $inputData[self::FORM_TOGGLE_REALTOR];

        if($toggleOp) {
            $this->removeOperator($player->getName());
        } else {
            $this->addOperator($player->getName());
        }

        $player->setOp($toggleOp);
        $player->getProfile()->administrator = $toggleAdmin;
        $player->getProfile()->builder = $toggleBuilder;
        $player->getProfile()->realtor = $toggleRealtor;

        $this->profileProvider->saveProfile($player);

        $player->kick("§eИзменения применены.");
    }
}