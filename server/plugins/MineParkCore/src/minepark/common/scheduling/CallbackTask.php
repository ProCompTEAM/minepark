<?php
namespace minepark\common\scheduling;

use pocketmine\scheduler\Task;

class CallbackTask extends Task
{
    protected mixed $callable;
    
    protected array $arguments;
    
    public function __construct(callable $callable, array $arguments = [])
    {
        $this->callable = $callable;
        $this->arguments = $arguments;
        $this->arguments[] = $this;
    }

    public function getCallable() : Callable
    {
        return $this->callable;
    }

    public function onRun(): void
    {
        call_user_func_array($this->callable, $this->arguments);
    }
}