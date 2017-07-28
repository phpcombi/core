<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};


/**
 * # 框架运行时对象
 *
 * 自动创建唯一单例。
 *
 * @author andares
 */
class Runtime extends core\Meta\Container {
    use core\Traits\Singleton,
        core\Meta\Extensions\Overloaded;
}
