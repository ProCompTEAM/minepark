<?php
namespace minepark\commands;

use minepark\Providers;
use pocketmine\event\Event;
use minepark\defaults\Sounds;

use pocketmine\world\Position;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\providers\BankingProvider;
use minepark\providers\MapProvider;
use minepark\providers\ProfileProvider;

class GetOrganisationCommand extends Command
{
    public const CURRENT_COMMAND = "getorg";

    public const DEFAULT_POINT_NAME = "Мэрия";

    public const DEFAULT_POINT_DISTANCE = 20;

    private MapProvider $mapProvider;

    private BankingProvider $bankingProvider;

    private ProfileProvider $profileProvider;

    public function __construct()
    {
        $this->mapProvider = Providers::getMapProvider();

        $this->bankingProvider = Providers::getBankingProvider();

        $this->profileProvider = Providers::getProfileProvider();
    }

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
        $plist = $this->mapProvider->getNearPoints($position, self::DEFAULT_POINT_DISTANCE); 
        
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
        if ($this->bankingProvider->takePlayerMoney($player, 1000)) {
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
        $player->getSettings()->organisation = $orgId; 
        $this->profileProvider->saveSettings($player);

        $player->sendMessage("CommandGetOrgGet");
    }
}