<?php
namespace minepark;

use minepark\utils\CallbackTask;
use minepark\defaults\TimeConstants;
use minepark\models\RepeatingActionStates;

class Tasks
{
    private static array $repeatingActions = [];

    public static function initializeAll()
    {
        self::startRepeatingActionsCaller();
    }

    public static function executeActionAsync(callable $target, array $arguments = [])
    {
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

    public static function registerRepeatingAction(int $secondsInterval, callable $target, bool $executeAsync = false, array $arguments = [])
    {
        $states = new RepeatingActionStates;
        $states->target = $target;
        $states->executeAsync = $executeAsync;
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
                    self::callRepeatingAction($repeatingAction);
                }
            }
        });

        Core::getActive()->getScheduler()->scheduleRepeatingTask($task, TimeConstants::ONE_SECOND_TICKS);
    }

    private static function callRepeatingAction(RepeatingActionStates $repeatingAction)
    {
        if($repeatingAction->executeAsync) {
            self::executeActionAsync($repeatingAction->target, $repeatingAction->arguments);
        } else {
            call_user_func_array($repeatingAction->target, $repeatingAction->arguments);
        }
    }
}
?>