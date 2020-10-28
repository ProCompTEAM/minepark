<?php
namespace minepark\modules;

use minepark\Core;

class WorldProtector
{
	public function getCore() : Core
	{
		return Core::getActive();
	}
    
    
}
?>