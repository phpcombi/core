<?php

namespace Combi\Tris;

use Psr;
use Combi\Common\Traits;

class Logger extends \Psr\Log\NullLogger
{
    use Traits\Singleton;
}
