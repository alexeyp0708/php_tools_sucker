<?php


namespace Alpa\Tools\Sucker;


class Helpers
{
    /**
     * @param object|string $subject
     * @param string $prop
     * @return bool
     */
    static public function isUndefinedProperty($subject,string $prop,$returnError=false)
    {
        return self::sbox(function() use ($prop,$returnError,$subject){
            $check = false;
            set_error_handler(function (...$args) use (&$check,$returnError,$subject) {
                $check=true;
                if(substr($args[1],0,19)==='Undefined property:'){
                    return !$returnError;
                } else {
                    return false;
                }
            },E_NOTICE|E_WARNING);
            if(is_string($subject)){
                self::${$prop};
            } else {
                $this->$prop;
            }
            restore_error_handler();
            return $check;
        },$subject);
    }
    static public function & getRefProperty($subject,string $prop)
    {
        $res=null;
        if(Helpers::isUndefinedProperty($subject,$prop,true)){
            return $res;
        } else {
            return self::sbox(function()use ($prop,$subject){
                if(is_string($subject)){
                    self::${$prop};
                } else {
                    $this->$prop;
                }
            },$subject);
        }
    }
    public static function & sbox(\Closure $call, $target, ?string $slaveClass = null, array $args = [])
    {
        $slaveClass = !empty($slaveClass) ? $slaveClass : (is_string($target) ? $target : get_class($target));
        $target = !is_string($target) ? $target : null;
        $call = $call->bindTo($target, $slaveClass);
        self::refNoticeErrorHandler();
        $answer= & $call(...$args);
        restore_error_handler();
        return $answer;
    }
    
    private static function refNoticeErrorHandler(bool $prev_restore = false)
    {
        $prev_handler_error = null;
        $prev_handler_error = set_error_handler(function (...$args) use (&$prev_handler_error, $prev_restore) {

            if (in_array($args[1],[
                'Only variables should be assigned by reference',
                'Only variable references should be returned by reference'])) {
                return true;
            }
            if(!is_null($prev_handler_error)){
                $answer=$prev_handler_error(...$args);
                if(is_bool($answer)){
                    return $answer;
                }
            }
            return false;
        }, E_NOTICE|E_WARNING);
    }
}