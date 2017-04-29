<?php

namespace Combi\Access\Http;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Core\Business;
use Psr\Http\Message\ResponseInterface;

class Response extends Business\Result implements ResponseInterface
{

}