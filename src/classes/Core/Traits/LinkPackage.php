<?php

namespace Combi\Core\Traits;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
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

    protected $_inner_name = '';

    public function linkPackage(\Combi\Package $package, string $name): self {
        $this->_link_package    = $package;
        $this->_inner_name      = $name;
        return $this;
    }

    public function p(): \Combi\Package {
        return $this->_link_package;
    }

    public function getInnerName(): string {
        return $this->_inner_name;
    }
}
