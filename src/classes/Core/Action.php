<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 * Action 容器
 *
 *
 * @author andares
 */
abstract class Action extends core\Meta\Container
{
    use core\Meta\Extensions\Overloaded;

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
            throw abort::runtime(
                'Action [%id%] stack call sequence error. should be [%id2%]')
                    ->set('id',     $this->getActionId())
                    ->set('id2',    $stack[0]->getActionId());
        }
        if ($this->_action_done) {
            throw abort::runtime('Action [%id%] is done, can not run again')
                ->set('id', $this->getActionId());
        }

        // 业务逻辑
        try {
            core::hook()->take(\Combi\HOOK_TICK);
            core::hook()->take(\Combi\HOOK_ACTION_BEGIN, $this);

            $result = $this->handle(...$arguments);

            core::hook()->take(\Combi\HOOK_ACTION_END, $this, $result);
        } catch (\Throwable $e) {
            core::hook()->take(\Combi\HOOK_ACTION_BROKEN, $this, $e);
        }

        // 关闭action并出栈
        $this->_action_done = true;
        $action = $stack->pop();
        if ($action != $this) {
            throw abort::runtime(
                'Action [%id%] stack pop sequence error. should be [%id2%]')
                    ->set('id',     $this->getActionId())
                    ->set('id2',    $action->getActionId());
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
        $provider = core::config('settings')->auth;
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
