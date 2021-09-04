<?php
declare(strict_types = 1);

namespace Lolya;

use Lolya\Shoot;
use Lolya\Loader;
use Lolya\GunData;
use minepark\Core;
use Lolya\GunListener;
use minepark\Providers;
use pocketmine\world\World;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\command\Command;

use Lolya\creature\BulletEntity;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use minepark\providers\MapProvider;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\EntityFactory;
use pocketmine\command\CommandSender;
use pocketmine\entity\EntityDataHelper;
use minepark\defaults\OrganisationConstants;
use pocketmine\data\bedrock\EntityLegacyIds;
use minepark\components\organisations\Organisations;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class MainClass extends PluginBase implements Listener 
{
    public const POINT_NAME = "Оружейная";
    public const POINT_DISTANCE = 6;

    public $gunData;
    public $logger;
    public $shoot;
    public $listener;
    public $loader;

    public function onEnable() : void
    {
        $this->logger = $this->getServer()->getLogger();
        $this->shoot = new Shoot($this);
        $this->gunData = new GunData($this);
        $this->loader = new Loader($this);
        $this->listener = new GunListener($this);

        $this->consoleInfo("Â§eGuns Plugin!");
        
        $this->getServer()->getPluginManager()->registerEvents($this->listener, $this);

        EntityFactory::getInstance()->register(BulletEntity::class, function(World $world, CompoundTag $nbt) : BulletEntity{
            return new BulletEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['Bullet', EntityIds::SNOWBALL], EntityLegacyIds::SNOWBALL);
    }

    public function getCore() : Core
    {
        return Core::getActive();
    }

    public function getMapProvider() : MapProvider
    {
        return Providers::getMapProvider();
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, $args) : bool
    {
        if ($command->getName() !== "guns") return false;

        if (!$sender instanceof Player) {
            $sender->sendMessage("Простите, но данная команда доступна только игроку.");
            return true;
        }

        if (!$this->checkPlayer($sender)) {
            return true;
        }
        
        if (empty($args[0])) {
            $sender->sendMessage("Вы не указали подкоманду.");
            return true;
        }
        
        if ($args[0] == "get")
        {
            if (empty($args[1])) {
                $sender->sendMessage("Правильное использование данной команды: /guns get (WEAPON_ID)");
                return true;
            }
            if (!is_numeric($args[1])) {
                $sender->sendMessage("Простите, но второй аргумент должен быть цифрой.");
                return true;
            }
            
            $gun = $this->getGun(intval($args[1]));
            
            if (!$gun) {
                $sender->sendMessage("Данной пушки не существует :(");
                return true;
            }
            
            $this->giveGun($gun, $sender);
            return true;
        }
        
        if ($args[0] == "getammo")
        {		
            if (empty($args[1]) OR empty($args[2])) {
                $sender->sendMessage("Правильное использование данной команды: /guns getammo (AMMO_ID) (COUNT)");
                return true;
            }

            if (!is_numeric($args[1]) OR !is_numeric($args[2])) {
                $sender->sendMessage("Простите, но второй и третий аргумент должен быть цифрой.");
                return true;
            }
            
            $ammo = $this->getAmmo(intval($args[1]));
            
            if (!$ammo) {
                $sender->sendMessage("Данного типа патронов не существует :(");
                return true;
            }

            if ($args[2] > 32) {
                $sender->sendMessage("За раз можно взять только 32 патронов!");
                return true;
            }

            $this->giveAmmo($ammo, $sender, intval($args[2]));
            return true;
        }

        if ($args[0] == "info") {
            $generated = "---- ИНФОРМАЦИЯ ОБ ОРУЖИЯХ ----";

            foreach($this->getGunData()->getGuns() as $gunName => $gunData) {
                $gunName = intval(substr($gunName, 3));

                $generated .= "\nID " . $gunName . " -  ".$gunData['name'];
            }

            $sender->sendMessage($generated);
        }

        return false;
    }

    private function checkPlayer(Player $player) : bool
    {
        if ($this->getServer()->isOp($player->getName())) {
            return true;
        }

        if ($player->org == OrganisationConstants::SECURITY_WORK) {
            if ($player->getLocation()->distance($this->getMapProvider()->getPointPosition(self::POINT_NAME)) <= self::POINT_DISTANCE) {
                return true;
            }

            $player->sendMessage("Подойдите к оружейной ближе!");
            return false;
        }

        $player->sendMessage("Вы не работник службы охраны.");
        return false;
    }
    
    public function consoleAlert($msg)
    {
        $this->logger->alert($msg);
    }

    public function consoleInfo($msg)
    {
        $this->logger->info($msg);
    }
    
    public function getWeaponInHand(Player $player)
    {
        $weapon = $player->getInventory()->getItemInHand();
        
        return $weapon;
    }
    
    public function getWeaponInfo($weaponId)
    {
        return $this->getGunData()->getGun(intval($weaponId));
    }
    
    public function getShootClass()
    {
        return $this->shoot;
    }
    
    public function getGunData()
    {
        return $this->gunData;
    }
    
    public function getGun($weaponId)
    {
        $weapon = $this->getWeaponInfo($weaponId);
        
        if (!$weapon) {
            return false;
        }

        $generateditem = ItemFactory::getInstance()->get($weaponId);
        $weapon['id'] = intval($weaponId);
        $generateditem->setCustomName($weapon['name']);
        return $generateditem;
    }
    
    public function getAmmo($gunId)
    {
        $theAmmo = ItemFactory::getInstance()->get(262);
        $gunConfig = $this->getGunData()->getGun(intval($gunId));

        if (!$gunConfig) {
            return false;
        }
        
        $theAmmo->setCustomName("Патроны от оружия " . $gunConfig['name']);
        return $theAmmo;
    }
    
    public function giveAmmo($ammo, Player $player, $count=1)
    {
        $ammo->setCount($count);
        
        $player->getInventory()->addItem($ammo);
    }

    public function giveGun($gun, $player)
    {
        $player->getInventory()->addItem($gun);
    }
}
?>