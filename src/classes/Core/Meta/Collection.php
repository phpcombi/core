<?php

namespace Combi\Core\Meta;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};


/**
 * 标准Collection基类
 *
 * @author andares
 */
class Collection
    implements Core\Interfaces\Collection, \IteratorAggregate
{
    use Container;
}
