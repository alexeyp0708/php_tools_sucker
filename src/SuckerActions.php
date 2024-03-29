<?php


namespace Alpa\Tools\Sucker;


final class SuckerActions
{
    private static array $actions = [];
    private static bool $init = false;
    private static array $cache = [];

    /**
     * @param string $action
     * @return \Closure
     */
    final static public function getAction(string $action, bool $is_static = false): \Closure
    {
        $method = 'action_' . ($is_static ? 'static_' . $action : $action);
        if (!isset(self::$cache[$method])) {
            self::$cache[$method] = self::{$method}();
        }
        return self::$cache[$method];
    }

    private static function action_get()
    {
        return function & ($member) {
            $error_msg = '';
            $error_code = 0;
            $prev_handlers = null;
            $check = false;
            $prev_handlers = set_error_handler(function (...$args) use (&$prev_handlers, &$check) {
                if (substr($args[1], 0, 19) === 'Undefined property:') {
                    $check = true;
                    $bt = debug_backtrace()[3];
                    $args[1] = $args[1] . ' in ' . $bt['file'] . ' on line ' . $bt['line'] . "\n";
                    if ($prev_handlers !== null) {
                        return $prev_handlers(...$args);
                    }
                }
                return false;
            }, E_NOTICE | E_WARNING);
            $res = $this->$member;
            restore_error_handler();
            if ($check) {
                return $res;
            }
            return $this->$member;
            /* $handler = set_error_handler(function (...$args) use (&$error_msg, &$error_code) {
                 if (substr($args[1], 0, 19) === 'Undefined property:') {
                     $bt = debug_backtrace()[3];
                     $error_msg = $args[1] . ' in ' . $bt['file'] . ' on line ' . $bt['line'] . "\n";
                     $error_code = $args[0];
                     return true;
                 }
                 return false;
             }, E_NOTICE | E_WARNING);
             $res = $this->$member;
             restore_error_handler();
             //For some reason, the restored handler does not run when trigger_error
             if ($error_code > 0) {
                 if ($handler !== null) {
                     set_error_handler($handler);
                 } // forcefully restore an error handler
                 trigger_error($error_msg, $error_code === E_NOTICE ? E_USER_NOTICE : E_USER_WARNING);
                 if ($handler !== null) {
                     restore_error_handler();
                 }
                 return $res;
             }
             return $this->$member;*/
        };
    }

    private static function action_static_get()
    {
        return function & ($member) {
            return self::${$member};
        };
    }

    private static function action_set()
    {
        return function ($member, &$value) {
            $this->$member = &$value;
        };
    }

    private static function action_static_set()
    {
        return function ($member, &$value) {
            self::$$member = &$value;
        };
    }

    private static function action_call()
    {
        return function & ($member, &...$args) {
            if ((new \ReflectionMethod($this, $member))->returnsReference()) {
                $answer = &$this->$member(...$args);
            } else {
                $answer = $this->$member(...$args);
            }
            return $answer;
        };
    }

    private static function action_static_call()
    {
        return function & ($member, &...$args) {
            if ((new \ReflectionMethod(self::class, $member))->returnsReference()) {
                $answer = &self::{$member}(...$args);
            } else {
                $answer = self::{$member}(...$args);
            }
            return $answer;
        };
    }

    private static function action_each()
    {
        return function ($each) {
            foreach ($this as $key => & $value) {
                if (true === $each($key, $value)) {
                    break;
                };
            }
            unset($value);
        };
    }

    private static function action_static_each()
    {
        return function ($each) {
            $vars = (new \ReflectionClass(self::class))->getStaticProperties();
            foreach ($vars as $key => & $value) {
                $value = &self::$$key;
                if (true === $each($key, $value)) {
                    break;
                };
            }
            unset($value);
        };
    }

    private static function action_isset()
    {
        return function ($member) {
            return isset($this->$member);
        };
    }

    private static function action_static_isset()
    {
        return function ($member) {
            return isset(self::${$member});
        };
    }

    private static function action_unset()
    {
        return function ($member) {
            unset($this->$member);
        };
    }

    private static function action_static_unset()
    {
        return function ($member) {
            //Generate Error
            //trigger_error("Attempt to unset static property ".self::class."::$ ".$member,E_USER_ERROR);
            unset(self::${$member});
        };
    }
}
