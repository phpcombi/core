<?php

namespace Combi\Core\Business;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Meta;

class Params extends Meta\Collection
{
    protected $message = null;

    public function setMessage(Message $message) {
        $this->message = $message;
    }
}