<?php

namespace Combi\Core\Traits;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

/**
 *
 * @author andares
 */
trait LinkPackage {
    /**
     * @var Package
     */
    protected $_link_package = null;

    public function linkPackage(\Combi\Package $package): self {
        $this->_link_package    = $package;
        return $this;
    }

    public function p(): \Combi\Package {
        return $this->_link_package;
    }
}
