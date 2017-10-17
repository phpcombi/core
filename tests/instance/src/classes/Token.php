<?php

namespace App;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

class Token extends \Combi\Action\Token
{
    protected static $keySecret     = 'mykeysecret1234';
    protected static $codeSecret    = 'mycodesecret1234';

}
