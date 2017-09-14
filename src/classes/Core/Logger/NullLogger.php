<?php

namespace Combi\Core\Logger;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

class NullLogger extends \Psr\Log\NullLogger
{
    use Core\Traits\Singleton;
}
