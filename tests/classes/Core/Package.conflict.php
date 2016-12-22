<?php
namespace A;

$_root_ = realpath(__DIR__ . '/../../..');

require "$_root_/tests/bootstrap.php";

class P extends \Combi\Core\Package {
    public function bootstrap(): bool {
        return true;
    }
}

namespace B;

class P extends \Combi\Core\Package {
    public function bootstrap(): bool {
        return true;
    }
}

combi()->register(\A\P::class, __DIR__);
combi()->register(\B\P::class, __DIR__);

var_dump(combi());

var_dump(combi()->a->pid());
var_dump(combi()->b->pid());

die(1);
