<?php
namespace minepark\mdc\sources;

use minepark\Core;
use minepark\mdc\dto\BaseDto;

abstract class RemoteSource
{
    public abstract function getName() : string;

    protected function createDto(array $data) {}

    public function createRequest(string $remoteMethod, $data)
    {
        return Core::getActive()->getMDC()->createRequest($this->getName(), $remoteMethod, $data);
    }
}
?>