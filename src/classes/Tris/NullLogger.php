<?php

namespace Combi\Tris;

use Psr;
use Combi\Utils\Traits;

class Logger extends \Psr\Log\NullLogger
{
    use Traits\Singleton;
}
