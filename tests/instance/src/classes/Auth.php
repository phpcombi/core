<?php

namespace App;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Action,
    Runtime as rt
};

class Auth extends \Combi\Action\Auth
{
    protected function loadDataById($id): void {
        if ($id) {
            $redis  = rt::core()->redis();
            $key    = "app:models:user:$id";
            $data   = $redis->get($key);
            $this->data = $data ? unserialize($data) : null;
        }
    }

    protected function loadPermById($id): void {
        if ($id) {
            $this->perm = Action\PermMaster::instance();
        }
    }

    protected function loadSessionById($id): void {
        if ($id) {
            $this->session = Action\Session::instance($id);
            $this->session->load();
        }
    }
}
