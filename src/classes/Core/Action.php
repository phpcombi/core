<?php

namespace Combi\Core;

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
abstract class Action extends Meta\Container
    implements Interfaces\LinkPackage
{
    use Meta\Extensions\Overloaded,
        Traits\LinkPackage {}

    private static $_action_stack = null;

    private $_action_previous = null;

    private $_action_id;

    private $_action_done = false;

    protected $_auth = null;

    public function __construct() {
        $this->_action_id = static::genActionId();

        $stack = self::getActionStack();
        if (isset($stack[0])) {
            $this->_action_previous = $stack[0];
        }

        $this->setAuth($this->genAuth());

        // 入栈
        $stack[] = $this;
    }

    final public function __invoke(...$arguments) {
        $stack = self::getActionStack();

        // action栈判断
        if ($stack[0] != $this) {
            throw new \RuntimeException(
                "Action ".$this->getActionId().
                    " stack call sequence error. should be ".$stack[0]->getActionId());
        }
        if ($this->_action_done) {
            throw new \RuntimeException("Action ".
                $this->getActionId()." is done, can not run again");
        }

        // 业务逻辑
        $result = null;
        try {
            $hook = rt::core()->hook();
            $hook->take(\Combi\HOOK_TICK);
            $hook->take(\Combi\HOOK_ACTION_BEGIN, $this);

            $result = $this->handle(...$arguments);

            $hook->take(\Combi\HOOK_ACTION_END, $this, $result);
        } catch (\Throwable $e) {
            $hook->take(\Combi\HOOK_ACTION_BROKEN, $this, $e);
        }

        // 关闭action并出栈
        $this->_action_done = true;
        $action = $stack->pop();
        if ($action != $this) {
            throw new \RuntimeException(
                "Action ".$this->getActionId().
                    " stack pop sequence error. should be ".$action->getActionId());
        }

        return $result;
    }

    final public static function getActionStack(): \SplStack {
        !self::$_action_stack && (self::$_action_stack = new \SplStack)
            ->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO
                | \SplDoublyLinkedList::IT_MODE_KEEP);
        return self::$_action_stack;
    }

    protected static function genActionId(): string {
        return helper::gen_id();
    }

    public function getActionId() {
        return $this->_action_id;
    }

    protected function genAuth(): Auth {
        $provider = rt::core()->config('settings')->auth;
        if ($this->_action_previous) {
            $provider->attributes[] = $this->_action_previous->getAuth();
        }
        return helper::instance($provider);
    }

    public function setAuth(Auth $auth): void {
        $this->_auth = $auth;
    }

    public function getAuth(): Auth {
        return $this->_auth;
    }

    abstract protected function handle();
}
