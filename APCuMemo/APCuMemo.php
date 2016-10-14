<?php

namespace APCuMemo;

class APCuMemo
{
    /**
     * Provides basic memoization on function's arguments.
     * @param $function Function taking all provided arguments ($args) and computing the result when cache doesn't exist.
     * @param $opts Array of options. Supported are:
     *              - ttl (Time To Live)
     *              - renew (when true every fetch from cache will reset the ttl), default true
     *              - validate (function that decides whether value should be cached or not), default null
     * @param $args Arguments on which function's result depends. They have to be convertible to string.
     * @return mixed Returns whatever provided function returns.
     */
    public static function memoize($function, array $opts, string $name, ...$args)
    {
        $key = $name . "_" . implode("$", array_map("sha1", $args));
        if (apcu_exists($key)) {
            $fetched = false;
            $value = apcu_fetch($key, $fetched);
            if ($fetched) {
                if (!array_key_exists('renew', $opts) || $opts['renew'] === true) {
                    apcu_delete($key);
                    apcu_store($key, $value, array_key_exists('ttl', $opts) ? $opts['ttl'] : 3600);
                }
            } else {
                $value = call_user_func_array($function, $args);
                $store = array_key_exists('validate', $opts) ? call_user_func($opts['validate'], $value) : true;
                if ($store === true) {
                    apcu_store($key, $value, array_key_exists('ttl', $opts) ? $opts['ttl'] : 3600);
                }
            }
            return $value;
        } else {
            $value = call_user_func_array($function, $args);
            $store = array_key_exists('validate', $opts) ? call_user_func($opts['validate'], $value) : true;
            if ($store === true) {
                apcu_store($key, $value, array_key_exists('ttl', $opts) ? $opts['ttl'] : 3600);
            }
            return $value;
        }
    }
}

