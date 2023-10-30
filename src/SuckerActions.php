<?php


namespace Alpa\Tools\Sucker;


final class SuckerActions
{
    private static array $actions=[];
    private static bool $init=false;
    /**
     * @param string $action 
     * @return \Closure
     */
    final static public function getAction(string $action,bool $is_static=false):\Closure
    {
        $method=$is_static?'static_'.$action:$action;
       
        return self::$actions[$method];
    }
    public static function init()
    {
        if(self::$init){
            return;    
        }
        self::$actions=[
            'get'=> function & ($member) {
                $error_msg = '';
                $error_code = 0;
                $handler = set_error_handler(function (...$args) use (&$error_msg, &$error_code) {
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
                return $this->$member;
            },
            'static_get'=> function & ($member) {
                return self::${$member};
            },
            'set'=>function  ($member,&$value) {
                $this->$member = &$value;
            },
            'static_set'=>function  ($member,&$value) {
                self::$$member = &$value;
            },
            'call'=>function & ($member, &...$args) {
                if ((new \ReflectionMethod($this, $member))->returnsReference()) {
                    $answer = &$this->$member(...$args);
                } else {
                    $answer = $this->$member(...$args);
                }
                return $answer;
            },
            'static_call'=>function & ($member, &...$args) {
                if ((new \ReflectionMethod(self::class,$member))->returnsReference()) {
                    $answer = &self::{$member}(...$args);
                } else {
                    $answer = self::{$member}(...$args);
                }
                return $answer;
            },
            'each'=>function ($each)  {
                foreach ($this as $key => & $value) {
                    if (true === $each($key, $value)) {
                        break;
                    };
                }
                unset($value);
            },
            'static_each'=>function ($each)  {
                $vars = (new \ReflectionClass(self::class))->getStaticProperties();
                foreach ($vars as $key => & $value) {
                    $value = & self::$$key;
                    if (true === $each($key, $value)) {
                        break;
                    };
                }
                unset($value);
            },
            'isset'=>function ($member) {
                return isset($this->$member);
            },
            'static_isset'=>function ($member) {
                return isset(self::${$member});
            },
            'unset'=>function ($member) {
                unset($this->$member);
            },
            'static_unset'=>function ($member) {
                //Generate System Error
                unset(self::${$member});
            }
        ];
    }
}
SuckerActions::init();