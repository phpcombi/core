<?php

namespace Combi\Utils\Pack;

use Combi\Common\Interfaces;

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
