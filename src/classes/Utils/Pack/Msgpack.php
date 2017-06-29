<?php

namespace Combi\Utils\Pack;

use Combi\Utils\Interfaces;

/**
 * Description of Msgpack
 *
 * @author andares
 */
class Msgpack implements Interfaces\Encoder {
    public function encode($value) {
        return \msgpack_pack($value);
    }

    public function decode($data) {
        return \msgpack_unpack($data);
    }
}
