<?php

namespace Combi\Tris;

use Psr;
use Combi\Traits;

class Logger extends \Psr\Log\NullLogger
{
    use Traits\Instancable;
}
