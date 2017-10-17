<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Runtime as rt
};

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
     * @var Meta\Collection
     */
    protected $extra;

    public function __construct(\Throwable $e) {
        parent::__construct($e->getMessage(), $e->getCode(), $e);

        $this->extra = new Meta\Collection;
    }

	public function __toString(): string {
        return json_encode($this);
    }

    public function message(): string {
        return helper::padding($this->getPrevious()->getMessage(),
            $extra  = $this->all());
    }

    public function toArray(): array {
        $exc    = $this->getPrevious();
        $result = [
            'message'   => $this->message(),
            'code'      => $exc->getCode(),
            'file'      => $exc->getFile(),
            'line'      => $exc->getLine(),
            'extra'     => $this->all(),
        ];
        return $result;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    public function class(): string {
        return get_class($this->getPrevious());
    }

    public function extra(): \Meta\Collection {
        return $this->extra;
    }

    public function set($key, $value): self {
        $this->extra->set($key, $value);
        return $this;
    }

    public function get($key) {
        return $this->extra->get($key);
    }

    public function level(string $level): self {
        $this->set('__level', $level);
        return $this;
    }

    public function __call(string $name, array $arguments) {
        return $this->extra->$name(...$arguments);
    }

    public function __debugInfo() {
        return $this->toArray();
    }
}
