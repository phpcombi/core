<?php

namespace Combi\Utils\Interfaces;

/**
 *
 * @author andares
 */
interface Encoder {
    public function encode($data);
    public function decode($data);
}
