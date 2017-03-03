<?php

namespace Combi;

use Combi\Base;
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
    use Meta\Overloaded;

    /**
     * 数据
     * @var Base\Container
     */
    protected $extra;

    public function __construct(\Throwable $e) {
        parent::__construct($e->getMessage(), $e->getCode(), $e);

        $this->extra = new Base\Container;
    }

	public function __toString(): string {
        return json_encode($this);
    }

    public function jsonSerialize(): array {
        $extra  = $this->toArray();
        $exc    = $this->getPrevious();
        $result = [
            'message'   => $exc->getMessage(),
            'code'      => $exc->getCode(),
            'file'      => $exc->getFile(),
            'line'      => $exc->getLine(),
            'extra'     => $extra,
        ];
        return $result;
    }

    public function class(): string {
        return get_class($this->getPrevious());
    }

    public function getExtra(): Base\Container {
        return $this->extra;
    }

    public function set($key, $value): self {
        $this->extra->set($key, $value);
        return $this;
    }

    public function __call(string $name, array $arguments = []) {
        return $this->extra->$name(...$arguments);
    }

    public function __debugInfo() {
        return $this->jsonSerialize();
    }
}
