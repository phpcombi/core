<?php

namespace Combi\Core\Logger;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

class NullLogger extends \Psr\Log\NullLogger
{
    use core\Traits\Singleton;
}
