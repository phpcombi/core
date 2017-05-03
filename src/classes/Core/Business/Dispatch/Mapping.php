<?php

namespace Combi\Core\Business\Dispatch;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Core\Business\Dispatcher;
use Combi\Meta;
use Combi\Utils\CacheFile;

/**
 *  路由映射策略
 */
class Mapping
{
    const VALUETAG = 0;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $current_space    = '';

    /**
     * @var string
     */
    private $current_prefix   = '';

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher = null) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string $space
     * @return self
     */
    public function space(string $space = ''): self {
        $this->current_space = $space;
        return $this;
    }

    /**
     * @param string $prefix
     * @return self
     */
    public function prefix(string $prefix = ''): self {
        $this->current_prefix = $prefix;
        return $this;
    }

    /**
     * 映射别名
     *
     * @param string $alias
     * @param string $class
     * @return self
     */
    public function mapping(string $alias, string $class): self {
        $this->current_prefix   && $alias   = "$this->current_prefix.$alias";
        $this->current_space    && $class   = "$this->current_space\\$class";

        $path = explode('.', $alias);
        self::append($path, $class, $this->data);
        return $this;
    }

    /**
     * @param callable $builder
     * @return void
     */
    public function load(callable $builder): void {
        if (!$this->dispatcher) {
            throw abort::runtime("dispatch mapping need dispatcher to make cache");
        }
        $cache = new CacheFile(function() {
            return $this->dispatcher->innerPackage()->dir('tmp', 'dispatcher'.
                DIRECTORY_SEPARATOR.'mapping');
        }, function() {
            return $this->dispatcher->innerPackage()
                ->pid().'-'.$this->dispatcher->innerName().'.php';
        }, function() use ($builder) {
            $builder($this);
            return "<?php\nreturn ".var_export($this->data, true).";";
        });

        $this->data = $cache->load();
    }

    public function __invoke(string $command) {
        $cursor = 0;
        $data   = $this->data;
        $path   = explode('.', $command);
        foreach ($path as $name) {
            if (!isset($data[$name])) {
                break;
            }
            $data = $data[$name];
            $cursor++;
        }

        if (isset($data[self::VALUETAG]) && $cursor) {
            if ($cursor < count($path)) {
                $command = $data[self::VALUETAG].'\\'.
                    str_replace(' ', '\\',
                        ucwords(implode(' ', array_slice($path, $cursor))));
            } else {
                $command = $data[self::VALUETAG];
            }
        } else {
            $command = ucfirst($command);
        }
        return $command;
    }

    /**
     * @param array &$path
     * @param string $class
     * @param array &$tree
     * @return void
     */
    private static function append(array &$path, string $class, array &$tree): void {
        $domain = array_shift($path);
        !isset($tree[$domain]) && $tree[$domain] = [];

        if ($path) {
            self::append($path, $class, $tree[$domain]);
        } else {
            $tree[$domain][self::VALUETAG] = $class;
        }
    }
}