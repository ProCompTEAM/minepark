<?php
namespace minepark\providers\base;

use minepark\Core;

abstract class DataProvider extends Provider
{
    public abstract function getName() : string;

    protected function createDto(array $data) {}

    public function createRequest(string $remoteMethod, $data)
    {
        return $this->getCore()->getMDC()->createRequest($this->getName(), $remoteMethod, $data);
    }
}
?>