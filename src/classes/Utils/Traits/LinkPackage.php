<?php

namespace Combi\Utils\Traits;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Utils\Interfaces;
use Combi\Core\Package;

/**
 *
 * @author andares
 */
trait LinkPackage {
    /**
     * @var Package
     */
    protected $_inner_package = null;

    protected $_inner_name = '';

    public function linkPackage(Package $package, string $name): self {
        $this->_inner_package   = $package;
        $this->_inner_name      = $name;
        return $this;
    }

    public function innerPackage(): Package {
        return $this->_inner_package;
    }

    public function innerName(): string {
        return $this->_inner_name;
    }
}
