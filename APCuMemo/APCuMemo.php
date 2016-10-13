<?php

namespace APCuMemo;

class APCuMemo
{
    /**
     * Provides basic memoization on function's arguments.
     * @param $function Function taking all provided arguments ($args) and computing the result when cache doesn't exist.
     * @param $opts Array of options. Currently only 'ttl' key is supported.
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
                apcu_delete($key);
                apcu_store($key, $value, array_key_exists('ttl', $opts) ? $opts['ttl'] : 3600);
            } else {
                $value = call_user_func_array($function, $args);
                apcu_store($key, $value, array_key_exists('ttl', $opts) ? $opts['ttl'] : 3600);
            }
            return $value;
        } else {
            $value = call_user_func_array($function, $args);
            apcu_store($key, $value, array_key_exists('ttl', $opts) ? $opts['ttl'] : 3600);
            return $value;
        }
    }
}

