<?php

namespace App;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

class App
{
    public $data;
    public $date = null;

    public function __construct(...$params) {
        $this->data = $params;
    }

    public function init(...$params) {
        $this->data = array_merge($this->data, $params);
    }

    public function setDate($date) {
        $this->date = $date;
    }
}
