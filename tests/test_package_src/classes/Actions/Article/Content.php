<?php

namespace Test\Actions\Article;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Test\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Core\Business\{
    Dispatcher,
    Group,
    Action,
    Params,
    Result
};

class Content extends Action
{
    public function main(): void {
        echo 222;
    }
}