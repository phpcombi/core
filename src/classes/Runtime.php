<?php

namespace Combi\Core;


/**
 * Description of Runtime
 *
 * @author andares
 */
class Runtime {
    use Components\Instancable;

    public function app(string $src) {
        return new App($src);
    }
}
