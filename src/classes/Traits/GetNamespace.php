<?php
namespace Combi\Traits;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

/**
 *
 * @author andares
 */
trait GetNamespace {
    public static function namespace(): string {
        return helper::namespace(static::class);
    }
}
