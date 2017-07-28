<?php

namespace Combi\Core\Utils\Pack;


use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};


/**
 * Description of Json
 *
 * @author andares
 */
class Json implements core\Interfaces\Encoder {
    private $options = 0;
    private $depth = 512;
    private $assoc = true;

    public function encode($value) {
        return \json_encode($value, $this->options, $this->depth);
    }

    public function decode($data) {
        return \json_decode($data, $this->assoc, $this->depth, $this->options);
    }

    public function configure(array $settings) {
        isset($settings['options']) && $this->options   = $settings['options'];
        isset($settings['depth'])   && $this->depth     = $settings['depth'];
        isset($settings['assoc'])   && $this->assoc     = $settings['assoc'];
    }
}
