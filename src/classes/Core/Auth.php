<?php

namespace Combi\Core;

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
class Auth implements Interfaces\Instancable
{

    protected $id = null;

    public static function instance(?self $previous = null): self {
        if ($previous) {
            return $previous;
        }
        return new static(mt_rand(100000, 999999));
    }

    public function __construct($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }
}
