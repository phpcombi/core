<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

/**
 * Description of Package
 *
 * @author andares
 */
class Package extends \Combi\Package {
    protected static $_pid = 'core';

    private static $_redis_instances = [];

    public function redis(string $name = 'default'): \Redis {
        if (!isset(self::$_redis_instances[$name])) {
            $config = $this->config('redis');
            if (!$config || !$config->$name) {
                throw new \RuntimeException("redis config $name is not exists");
            }

            self::$_redis_instances[$name] = new \Redis();
            if (isset($config->$name['connect'])) {
                self::$_redis_instances[$name]->connect(...$config->$name['connect']);
            } elseif (isset($config->$name['connect'])) {
                self::$_redis_instances[$name]->pconnect(...$config->$name['pconnect']);
            }

            $this->hook()->take(\Combi\HOOK_REDIS_UP, self::$_redis_instances[$name], $name);
        }

        return self::$_redis_instances[$name];
    }

    public function hook(): Hook {
        return Hook::instance();
    }

}
