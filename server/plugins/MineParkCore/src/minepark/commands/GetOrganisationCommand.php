<?php
namespace minepark\commands;

use minepark\Providers;
use pocketmine\event\Event;
use minepark\defaults\Sounds;

use pocketmine\level\Position;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;

class GetOrganisationCommand extends Command
{
    public const CURRENT_COMMAND = "getorg";

    public const DEFAULT_POINT_NAME = "Мэрия";

    public const DEFAULT_POINT_DISTANCE = 20;

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

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        $player->sendSound(Sounds::ROLEPLAY);

        if($this->getDefaultPoint($player->getPosition()) == null) {
            $player->sendMessage("CommandGetOrgNearGov");
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
        $plist = Providers::getMapProvider()->getNearPoints($position, self::DEFAULT_POINT_DISTANCE); 
        
        foreach($plist as $point)
        {
            if($point == self::DEFAULT_POINT_NAME) {
                return $point;
            }
        }

        return null;
    }

    private function sendMenu(MineParkPlayer $player)
    {
        $form = "";
        $form .= "§eВы можете устроиться в организацию";
        $form .= "\n§eСтоимость услуги - §31000р";
        $form .= "\n§dПродавец§3: §e/getorg §51";
        $form .= "\n§dМедик§3: §e/getorg §52";
        $form .= "\n§dГос.Служащий§3: §e/getorg §53";
        
        $player->sendWindowMessage($form);
    }

    private function switchOrg(MineParkPlayer $player, string $orgId)
    {
        if (Providers::getBankingProvider()->takePlayerMoney($player, 1000)) {
            switch($orgId)
            {
                case 1: $orgId = 5; break;
                case 2: $orgId = 2; break;
                case 3: $orgId = 3; break;
                default: $orgId = 0; break;
            }
            
            $this->setOrg($player, $orgId);
        } else {
            $player->sendMessage("CommandGetOrgNoMoney"); 
        }
    }

    private function setOrg(MineParkPlayer $player, string $orgId)
    {
        $player->getProfile()->organisation = $orgId; 
        $this->getCore()->getProfiler()->saveProfile($player);

        $player->sendMessage("CommandGetOrgGet");
    }
}
?>