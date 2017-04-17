<?php

namespace Combi\Common\Interfaces;

/**
 *
 * @author andares
 */
interface Encoder {
    public function encode($data);
    public function decode($data);
}
