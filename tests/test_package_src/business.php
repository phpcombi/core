<?php

namespace Test;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Test\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Core\Business;


// dispatcher

inner::instance()->dispatcher = new Business\Dispatcher(
    function(Business\Dispatch\Mapping $mapping)
{
    $mapping
        ->mapping('user',       Actions\User::class)
        ->mapping('article',    Actions\Article::class)

        ->space('Test\Actions')
            ->prefix('api')
                ->mapping('user',    'User')
                ->mapping('article', 'Article')

        ->space()->prefix();
});

// middlewares

inner::dispatcher()->addMiddlewares(new Middlewares\Throttle);

(new Middlewares\TestMW('custom params'))->attach(
    Actions\User::class,
    Actions\Article\Content::class
);
