<?php
declare(strict_types = 1);

namespace Kirill_Poroh;


use pocketmine\Player;
use Kirill_Poroh\CallbackTask;


class Marquee
{	
	private $plugin;
	
	public $marquee_text;
	public $marquee_number;
	public $marquee_offset;
	
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		
		$this->marquee_number = 0;
		
		$plugin->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "sendMarquee"]), 20);
		$plugin->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "updateMarquee"]), 20 * 60);
	}

	public function sendMarquee()
	{
		if($this->marquee_offset < 1) return;
		
		$output = $this->marquee_text;
		
		$output = substr($output, 0, $this->marquee_offset);
		$output = ( str_repeat(" ", 50 - mb_strlen($output)) . $output );
		
		foreach($this->plugin->getServer()->getOnlinePlayers() as $p)
		{
			$p->sendTip($output);
		}
		
		$this->marquee_offset--;
	}
	
	public function updateMarquee()
	{
		if(!file_exists($this->plugin->getDirectory() . "marquee.txt"))
			$this->marquee_text = "Файл текстов marquee.txt не настроен!";
		else
		{
			$texts = explode("\r\n", file_get_contents($this->plugin->getDirectory() . "marquee.txt"));
			
			if($this->marquee_number >= count($texts)) $this->marquee_number = 0;
			
			$this->marquee_text = "§0" . $texts[$this->marquee_number];
		}
		
		$this->marquee_offset = 50;
		$this->marquee_number++;
	}
}
