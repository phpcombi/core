<?php

namespace Combi\Core\Utils\Pack;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};


/**
 * Description of Msgpack
 *
 * @author andares
 */
class Msgpack implements Core\Interfaces\Encoder {
    public function encode($value) {
        return \msgpack_pack($value);
    }

    public function decode($data) {
        return \msgpack_unpack($data);
    }
}
