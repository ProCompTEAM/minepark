<?php
namespace minepark\command;

use minepark\Sounds;
use pocketmine\Player;
use minepark\Permissions;

use pocketmine\event\Event;
use pocketmine\level\Position;

class GetOrganisationCommand extends Command
{
    public const CURRENT_COMMAND = "getorg";

    public const DEFAUlT_POINT_NAME = "Мэрия";

    public const DEFAUlT_POINT_DISTANCE = 20;

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $player->sendSound(Sounds::ROLEPLAY);

        if($this->getDefaultPoint($player->getPosition()) == null) {
            $player->sendMessage("§сВам необходимо находиться в мэрии!");
            return;
        }

        if(self::argumentsNo($args)) {
            $this->sendMenu($player);
        } else {
            $this->switchOrg($player, $args[0]);
        }
    }

    private function getDefaultPoint(Position $position) : ?string
    {
        $plist = $this->getCore()->getMapper()->getNearPoints($position, self::DEFAUlT_POINT_DISTANCE); 
        
        foreach($plist as $point)
        {
            if($point == self::DEFAUlT_POINT_NAME) {
                return $point;
            }
        }

        return null;
    }

    private function sendMenu(Player $player)
    {
        $form = "";
        $form .= "§eВы можете устроиться в организацию";
        $form .= "\n§eСтоимость услуги - §31000р";
        $form .= "\n§dПродавец§3: §e/getorg §51";
        $form .= "\n§dМедик§3: §e/getorg §52";
        $form .= "\n§dГос.Служащий§3: §e/getorg §53";
        
        $player->sendWindowMessage($form);
    }

    private function switchOrg(Player $player, string $orgId)
    {
        if($this->getCore()->getBank()->takePlayerMoney($player, 1000)) {
            switch($orgId)
            {
                case 1: $orgId = 5; break;
                case 2: $orgId = 2; break;
                case 3: $orgId = 3; break;
                default: $orgId = 0; break;
            }
            
            $this->setOrg($player, $orgId);
        } else {
            $player->sendMessage("§cНе хватило суммы :("); 
        }
    }

    private function setOrg(Player $player, string $orgId)
    {
        $orgName = $this->getCore()->getOrganisationsModule()->getName($orgId);

        $player->getProfile()->organisation = $orgId; 
        $this->getCore()->getProfiler()->saveProfile($player);

        $player->sendMessage("Теперь вы $orgName");
    }
}
?>