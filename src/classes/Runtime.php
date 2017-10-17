<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort
};


/**
 * # 框架运行时对象
 *
 * 自动创建唯一单例。
 *
 * @author andares
 */
class Runtime extends Core\Meta\Collection {
    use Core\Traits\Singleton,
        Core\Meta\Extensions\Overloaded {}

    /**
     * @var array
     */
    public static $_singletons   = [];

    /**
     * @var Package
     */
    private static $_main_package = null;

    /**
     * @var array
     */
    private static $_env    = [
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
    private static $_is_uppped = false;

    /**
     * @var array
     */
    private static $_bootloads = [];

    public static function main(): Package {
        return self::$_main_package;
    }

    /**
     * @return bool
     */
    public static function isProd(): bool {
        return self::env('is_prod');
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
            $value !== null && self::$_env[$key] = $value;
            return self::$_env[$key] ?? null;
        }
        return self::$_env;
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
        if (self::instance()->has($pid)) {
            throw new \RuntimeException(get_class($package).
                " class package id $pid is conflicted");
        }
        self::instance()->set($pid, $package);

        self::$_bootloads[$pid] = $bootload;
    }

    /**
     * @param string|null $pid
     * @param array|null $config
     * @return void
     */
    public static function up(?string $pid = null, ?array $env = null): void
    {
        // 检查状态
        if (self::$_is_uppped) {
            return;
        }

        // 基础配置
        $env && self::$_env = $env;

        // 设置主包
        $pid && self::$_main_package = self::$pid();

        // 启动引导载入
        foreach (self::$_bootloads as $pid => $bootload) {
            foreach ($bootload as $filename) {
                require self::$pid()->path('src', "$filename.php");
            }
            unset(self::$_bootloads[$pid]);
        }

        // 设置状态
        self::$_is_uppped = true;

        // 开启catcher
        $catcher = self::core()->config('settings')->catcher;

        // 勾子
        $hook = self::core()->hook();
        register_shutdown_function(function() use ($hook) {
            $hook->take(\Combi\HOOK_SHUTDOWN);
        });
        $hook->take(\Combi\HOOK_READY);
        $hook->take(\Combi\HOOK_TICK);
    }

    public static function __callStatic(string $name, array $arguments) {
        return self::instance()->$name;
    }
}
