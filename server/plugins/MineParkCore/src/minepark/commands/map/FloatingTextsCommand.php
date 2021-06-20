<?php
namespace minepark\commands\map;

use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\map\FloatingTexts;
use minepark\defaults\Permissions;
use pocketmine\event\Event;

class FloatingTextsCommand extends Command
{
    private const NAME = "floatingtexts";

    private const CHOICE_CREATE = 0;
    
    private const CHOICE_REMOVE = 1;

    private FloatingTexts $floatingTexts;

    public function getCommand(): array
    {
        return [
            self::NAME
        ];
    }

    public function __construct()
    {
        $this->floatingTexts = Components::getComponent(FloatingTexts::class);
    }

    public function getPermissions(): array
    {
        return [
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), ?Event $event = null)
    {
        $this->floatingTexts->initializeMenu($player);
    }
}