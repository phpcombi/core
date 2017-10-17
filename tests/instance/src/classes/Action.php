<?php

namespace App;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

class Action extends \Combi\Action
{
    protected function handle() {
        echo "action ".$this->getActionId()." is running.\n";
    }
}
