<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort
};

class Core
{
    use Core\Traits\StaticAgent;

    /**
     * @var array
     */
    public static $singletons   = [];

    /**
     * @var array
     */
    private static $env    = [
        'is_prod'   => false,
        'scene'     => 'dev',

        'path'      => [
            'tmp'   => '/tmp/combi/tmp',
            'logs'  => '/tmp/combi/logs',
        ],
    ];

    /**
     * @var bool
     */
    private static $is_up = false;

    /**
     * @var Core\Package
     */
    private static $main_package = null;

    /**
     * @var array
     */
    private static $bootloads = [];

    public static function redis(string $name = 'default'): \Redis {
        static $redis_instances = [];

        if (!isset($redis_instances[$name])) {
            $config = self::config('redis');
            if (!$config || !$config->$name) {
                throw new \RuntimeException("redis config $name is not exists");
            }

            $redis_instances[$name] = new \Redis();
            if (isset($config->$name['connect'])) {
                $redis_instances[$name]->connect(...$config->$name['connect']);
            } elseif (isset($config->$name['connect'])) {
                $redis_instances[$name]->pconnect(...$config->$name['pconnect']);
            }

            self::hook()->take(HOOK_REDIS_UP, $redis_instances[$name], $name);
        }

        return $redis_instances[$name];
    }

    public static function hook(): Core\Hook {
        return Core\Hook::instance();
    }

    public static function rt(): Runtime {
        return Runtime::instance();
    }

    /**
     *
     *
     * @param string|null $key
     * @param mixed $value
     * @return mixed
     */
    public static function env(?string $key = null, $value = null) {
        if ($key) {
            $value !== null && self::$env[$key] = $value;
            return self::$env[$key] ?? null;
        }
        return self::$env;
    }

    /**
     * @return bool
     */
    public static function isProd(): bool {
        return self::env('is_prod');
    }

    /**
     *
     * @return Package
     */
    public static function main(): Package {
        return self::$main_package;
    }

    /**
     * 注册一个package到runtime
     *
     * @param Package $package
     * @return void
     */
    public static function register(Package $package, ...$bootload): void {
        $pid = $package->pid();

        if (!$pid) {
            throw new \RuntimeException("package id of ".
                get_class($package)." can not be null");
        }
        if (self::rt()->has($pid)) {
            throw new \RuntimeException(get_class($package).
                " class package id $pid is conflicted");
        }
        self::rt()->set($pid, $package);

        self::$bootloads[$pid] = $bootload;
    }

    /**
     * @param string|null $pid
     * @param array|null $config
     * @return void
     */
    public static function up(?string $pid = null,
        ?array $env = null): void
    {
        // 检查状态
        if (self::$is_up) {
            return;
        }

        // 基础配置
        $env && self::$env = $env;

        // 设置主包
        $pid && self::$main_package = self::rt()->$pid;

        // 启动引导载入
        foreach (self::$bootloads as $pid => $bootload) {
            foreach ($bootload as $filename) {
                require self::rt()->$pid->path('src', "$filename.php");
            }
            unset(self::$bootloads[$pid]);
        }

        // 开启catcher
        $provider = self::config('settings')->catcher;
        if ($provider) {
            helper::instance($provider);
        }

        // 勾子
        register_shutdown_function(function() {
            self::hook()->take(\Combi\HOOK_SHUTDOWN);
        });
        self::hook()->take(\Combi\HOOK_READY);
        self::hook()->take(\Combi\HOOK_TICK);

        // 设置状态
        self::$is_up = true;
    }

    public static function instance(): Core\Package {
        return Core\Package::instance();
    }

}
