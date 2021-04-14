<?php
namespace Lolya;

class GunData
{
    public $guns;
    public $main;
    
    public function __construct($mainClass)
    {
        $this->main = $mainClass;
    }
    
    public function addGun($itemId, $name, $sound, $damage=3, $burst=5)
    {
        if (!is_int($itemId)) {
            $this->main->consoleAlert("Specified itemId parameter in guns plugin is not integer. Function - addGun"); 
            return false; 
        }
        if (!is_int($damage)) {
            $this->main->consoleAlert("Specified damage parameter in guns plugin is not integer. Function - addGun"); 
            return false; 
        }
        
        $this->guns["id-$itemId"] = array(
            "name" => $name,
            "damage" => $damage,
            "burst" => $burst,
            "sound" => $sound
        );
    }
    
    public function getGuns()
    {
        return $this->guns;
    }
    
    public function getGun($gunId)
    {
        if (!is_int($gunId)) {
            $this->main->consoleAlert("Specified gunId parameter in guns plugin is not integer. Function - getGun.");
            return false;
        }

        $guns = $this->getGuns();
        $weaponId = "id-$gunId";
        if (!isset($guns[$weaponId]['name'])) {
            return false;
        } else {
            return $guns[$weaponId];
        }
    }
}
?>