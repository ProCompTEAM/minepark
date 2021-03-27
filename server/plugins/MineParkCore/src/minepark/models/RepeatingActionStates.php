<?php
namespace minepark\models;

class RepeatingActionStates
{
    public mixed $target;

    public bool $executeAsync;

    public array $arguments;

    public int $interval;

    public int $penIndex = 0;
}
?>