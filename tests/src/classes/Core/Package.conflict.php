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

combi()->register(\A\P::instance('a dir'));
combi()->register(\B\P::instance('b dir'));

var_dump(combi());

var_dump(combi()->a->pid());
var_dump(combi()->b->pid());

die(1);
