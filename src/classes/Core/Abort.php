<?php

namespace Combi\Core;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Meta;

/**
 * Description of Abort
 *
 * 在Log类中预留的context约定：
 * - level 设定日志级别，默认 LogicException 为 notice, RuntimeException 为 critical, ErrorException 和 Error 为 error, 其他均为warning
 *
 * @author andares
 */
class Abort extends \Exception implements \JsonSerializable
{
    /**
     * 数据
     * @var Meta\Container
     */
    protected $extra;

    public static function __callStatic(string $name, array $arguments): self {
        $class = ucfirst($name) . 'Exception';
        return self::with(new $class(...$arguments));
    }

    /**
     * @param \Throwable $e
     * @param callable $maker
     * @param array $arguments
     * @return Core\Abort
     */
    public static function with(\Throwable $e, callable $maker = null,
        ...$arguments): self
    {
        $abort = new static($e);
        return $maker ? $maker($abort, ...$arguments) : $abort;
    }

    public function __construct(\Throwable $e) {
        parent::__construct($e->getMessage(), $e->getCode(), $e);

        $this->extra = new Meta\Container;
    }

	public function __toString(): string {
        return json_encode($this);
    }

    public function toArray(): array {
        $extra  = $this->all();
        $exc    = $this->getPrevious();
        $result = [
            'message'   => helper::padding($exc->getMessage(), $extra),
            'code'      => $exc->getCode(),
            'file'      => $exc->getFile(),
            'line'      => $exc->getLine(),
            'extra'     => $extra,
        ];
        return $result;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    public function class(): string {
        return get_class($this->getPrevious());
    }

    public function extra(): Meta\Container {
        return $this->extra;
    }

    public function set($key, $value): self {
        $this->extra->set($key, $value);
        return $this;
    }

    public function __call(string $name, array $arguments) {
        return $this->extra->$name(...$arguments);
    }

    public function __debugInfo() {
        return $this->toArray();
    }
}
