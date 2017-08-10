<?php

namespace Combi\CoreTraits;

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
    protected $_inner_package = null;

    protected $_inner_name = '';

    public function linkPackage(\Combi\Package $package, string $name): self {
        $this->_inner_package   = $package;
        $this->_inner_name      = $name;
        return $this;
    }

    public function innerPackage(): \Combi\Package {
        return $this->_inner_package;
    }

    public function innerName(): string {
        return $this->_inner_name;
    }
}
