<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort
};

class Core extends Package
{
    protected static $pid = 'core';

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
                throw abort::runtime('redis config %name% is not exists.')
                    ->set('name', $name);
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

    public static function rt(): Core\Runtime {
        return Core\Runtime::instance();
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
     * @return Core\Package
     */
    public static function main(): Core\Package {
        return self::$main_package;
    }

    /**
     * 注册一个package到runtime
     *
     * @param Core\Package $package
     * @return void
     */
    public static function register(Core\Package $package, ...$bootload): void {
        $pid = $package->pid();

        if (!$pid) {
            throw abort::runtime('package id of %class% can not be null')
                    ->set('class', get_class($package));
        }
        if (self::rt()->has($pid)) {
            throw abort::runtime('%class package id %pid% is conflicted')
                    ->set('class', get_class($package))
                    ->set('pid', $pid);
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
        self::bootload();

        // 开启catcher
        self::enableCatcher();

        // 勾子
        register_shutdown_function(function() {
            core::hook()->take(\Combi\HOOK_SHUTDOWN);
        });
        core::hook()->take(\Combi\HOOK_READY);
        core::hook()->take(\Combi\HOOK_TICK);

        // 设置状态
        self::$is_up = true;
    }

    private static function bootload(): void {
        foreach (self::$bootloads as $pid => $bootload) {
            foreach ($bootload as $filename) {
                require self::rt()->$pid->path('src', "$filename.php");
            }
            unset(self::$bootloads[$pid]);
        }
    }

    /**
     * @return void
     */
    private static function enableCatcher(): void {
        $provider = self::config('settings')->catcher;
        if ($provider) {
            helper::instance($provider);
        }
    }

}
