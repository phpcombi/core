<?php

namespace App\Models;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

class User extends Core\Meta\Struct implements \Serializable
{
    use Core\Meta\Extensions\Serializable;

    protected static $_defaults = [
        'id'        => null,
        'name'      => null,
        'pass'      => null,
        'email'     => '',
        'nickname'  => null,
        'gender'    => 0,
        'birthday'  => '',
    ];

    protected function _confirm_id($val) {
        return $val ?: helper::genId();
    }

    protected function _confirm_nickname($val) {
        return $val ?: ucfirst($this->name);
    }
}
