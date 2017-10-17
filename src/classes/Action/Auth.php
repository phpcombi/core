<?php

namespace Combi\Action;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

/**
 * Auth
 *
 *
 * @author andares
 */
abstract class Auth
{

    protected $id;

    protected $token    = null;

    protected $session  = null;

    protected $perm     = null;

    protected $data     = null;

    protected $previous = null;

    abstract protected function loadDataById($id): void;
    abstract protected function loadPermById($id): void;
    abstract protected function loadSessionById($id): void;

    public function __construct($id = null) {
        $this->id = $id;
        $this->update();
    }

    public function withToken(Token $token): self {
        if ($token->getId() === $this->id()) {
            return $this;
        }

        $auth = clone $this;
        $auth->id       = $token->getId();
        $auth->token    = $token;
        $auth->previous = $this;
        return $auth->update();
    }

    public function id() {
        return $this->id;
    }

    public function token(): ?Token {
        return $this->token;
    }

    public function data() {
        return $this->data;
    }

    public function perm(): Interfaces\Perm {
        return $this->perm ?: $this->getDefaultPerm();
    }

    public function session(): Interfaces\Session {
        return $this->session ?: $this->getDefaultSession();
    }

    public function previous(): ?self {
        return $this->previous;
    }

    protected function update(): self {
        if (!$this->id) {
            $this->data = $this->perm = $this->session = null;
            return $this;
        }

        $this->loadDataById($this->id);
        $this->loadPermById($this->id);
        $this->loadSessionById($this->id);
        return $this;
    }

    protected function getDefaultSession(): Interfaces\Session {
        return SessionNull::instance();
    }

    protected function getDefaultPerm(): Interfaces\Perm {
        return PermDenied::instance();
    }
}
