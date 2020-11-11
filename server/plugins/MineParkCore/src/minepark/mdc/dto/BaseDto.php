<?php
namespace minepark\mdc\dto;

abstract class BaseDto
{
    public function set(array $data) {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}