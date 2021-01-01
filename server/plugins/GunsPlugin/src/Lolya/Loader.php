<?php
namespace Lolya;

class Loader
{
	public $main;
	
	public function __construct($mainClass)
	{
		$this->main = $mainClass;
		$this->load();
	}
	
	public function load()
	{
		$config = $this->getGunConfig();

		foreach($config as $name => $gun) {
			$this->main->getGunData()->addGun(intval(substr($name, 3)), $gun['name'], $gun['sound'], $gun['damage'], $gun['burst']);
		}
	}
	
	public function save()
	{
		file_put_contents("./core_data/weapons.json", json_encode($this->main->getGunData()->getGuns(), JSON_UNESCAPED_UNICODE));
	}

	public function getGunConfig()
	{
		return json_decode(file_get_contents("./core_data/weapons.json"), JSON_UNESCAPED_UNICODE);
	}
}
?>