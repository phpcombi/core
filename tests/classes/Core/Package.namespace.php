<?php
namespace Play;

$_root_ = realpath(__DIR__ . '/../../..');

require "$_root_/tests/bootstrap.php";

class Package extends \Combi\Core\Package {
    public function bootstrap(): bool {
        return true;
    }
}

combi()->register(Package::class, __DIR__);

die(1);
