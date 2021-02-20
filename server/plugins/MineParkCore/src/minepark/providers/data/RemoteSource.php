<?php
namespace minepark\providers\data;

use minepark\Core;

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