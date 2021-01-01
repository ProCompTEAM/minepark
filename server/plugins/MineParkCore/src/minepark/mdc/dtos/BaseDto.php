<?php
namespace minepark\mdc\dtos;

abstract class BaseDto
{
    public function set(array $data) {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}