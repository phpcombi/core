<?php

namespace Combi\Core\Interfaces;


use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 *
 * @author andares
 */
interface Encoder {
    public function encode($data);
    public function decode($data);
}
