<?php

namespace Combi\Action\Interfaces;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

/**
 *
 *
 * @author andares
 */
interface Session extends Core\Interfaces\Instancable
{
    public function load();
    public function save();
    public function setExpire(int $expire);
}
