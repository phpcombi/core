<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

/**
 * Action 容器
 *
 *
 * @author andares
 */
abstract class Action
    implements Core\Interfaces\Collection, \IteratorAggregate, Core\Interfaces\LinkPackage
{
    use Core\Meta\Container,
        Core\Meta\Extensions\Overloaded,
        Core\Traits\LinkPackage;

    /**
     * @var array
     */
    private static $_actived = [];

    /**
     * @var string
     */
    private $_id;

    /**
     * @var Action\Auth
     */
    protected $_auth;

    /**
     * @var array
     */
    protected $_arguments = [];

    abstract protected function handle();

    /**
     * @return array
     */
    public static function getActivedList(): array {
        return self::$_actived;
    }

    /**
     * @param Action\Auth|null $auth
     */
    public function __construct(?Action\Auth $auth = null) {
        $this->_auth = $auth;
        $this->genActionId();
    }

    /**
     * @param array $arguments
     * @return self
     */
    public function with(...$arguments): self {
        $action = clone $this;

        $action->genActionId();
        $action->_arguments = $arguments;

        return $action;
    }

    /**
     * @return mixed
     */
    public function getActionId() {
        return $this->_id;
    }

    /**
     * @param Action\Auth $auth
     * @return static
     */
    public function setAuth(Action\Auth $auth): self {
        $this->_auth = $auth;
        return $this;
    }

    /**
     * @return Action\Auth|null
     */
    public function auth(): ?Action\Auth {
        return $this->_auth;
    }

    /**
     * @return void
     */
    protected function genActionId(): void {
        $this->_id = helper::genId();
    }

    /**
     * @return mixed
     */
    final public function __invoke() {
        // 列入活动
        $this->setActionActive(true);

        // 业务逻辑
        $result = null;
        $error  = null;
        try {
            $hook = rt::core()->hook();
            $hook->take(\Combi\HOOK_TICK);
            $hook->take(\Combi\HOOK_ACTION_BEGIN, $this);

            $result = $this->handle(...$this->_arguments);
            $hook->take(\Combi\HOOK_ACTION_DONE, $result, $this);
        } catch (\Throwable $error) {
            $hook->take(\Combi\HOOK_ACTION_BROKEN, $error, $this);
        }
        $hook->take(\Combi\HOOK_ACTION_END, $result, $this, $error);

        // 移出活动
        $this->setActionActive(false);

        return $result;
    }

    /**
     * @param bool $active
     * @return void
     */
    final private function setActionActive(bool $active): void {
        if ($active) {
            self::$_actived[$this->getActionId()] = $this;
        } else {
            unset(self::$_actived[$this->getActionId()]);
        }
    }
}
