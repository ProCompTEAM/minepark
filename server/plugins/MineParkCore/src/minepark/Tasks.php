<?php
namespace minepark;

use minepark\defaults\TimeConstants;
use minepark\models\RepeatingActionStates;
use minepark\common\scheduling\CallbackTask;

class Tasks
{
    private static array $repeatingActions = [];

    public static function initializeAll()
    {
        self::startRepeatingActionsCaller();
    }

    public static function registerDelayedAction(int $ticks, callable $target, array $arguments = [])
    {
        $task = new CallbackTask($target, $arguments);
        Core::getActive()->getScheduler()->scheduleDelayedTask($task, $ticks);
    }

    public static function executeActionWithTicksInterval(int $ticks, callable $target, array $arguments = [])
    {
        $task = new CallbackTask($target, $arguments);
        Core::getActive()->getScheduler()->scheduleRepeatingTask($task, $ticks);
    }

    public static function registerRepeatingAction(int $secondsInterval, callable $target, array $arguments = [])
    {
        $states = new RepeatingActionStates;
        $states->target = $target;
        $states->arguments = $arguments;
        $states->interval = $secondsInterval;
        array_push(self::$repeatingActions, $states);
    }

    public static function cancelRepeatingAction(callable $target)
    {
        foreach(self::$repeatingActions as $repeatingActionKey => $repeatingAction) {
            if($repeatingAction->target == $target) {
                unset(self::$repeatingActions[$repeatingActionKey]);
                return;
            }
        }
    }

    private static function startRepeatingActionsCaller()
    {
        $task = new CallbackTask(function() {
            foreach(self::$repeatingActions as $repeatingAction) {
                $repeatingAction->penIndex++;
                if($repeatingAction->penIndex === $repeatingAction->interval) {
                    $repeatingAction->penIndex = 0;
                    call_user_func_array($repeatingAction->target, $repeatingAction->arguments);
                }
            }
        });

        Core::getActive()->getScheduler()->scheduleRepeatingTask($task, TimeConstants::ONE_SECOND_TICKS);
    }
}
?>