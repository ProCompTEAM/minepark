<?php
namespace minepark\mdc;

use minepark\Core;

class MDC
{
    public function getCore() : Core
    {
        return Core::getActive();
    }

    
}

?>