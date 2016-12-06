<?php

namespace Combi\Interfaces;

/**
 *
 * @author andares
 */
interface Encoder {
    public function encode($data);
    public function decode($data);
}
