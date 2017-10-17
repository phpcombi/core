<?php

namespace Combi\Action;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

/**
 *
 *
 * @author andares
 */
class Session extends Core\Meta\Collection implements Interfaces\Session
{
    use Core\Traits\Instancable,
        Core\Meta\Extensions\ChangeLog,
        Core\Meta\Extensions\Overloaded;

    protected static $_defaultConnectionName = 'default';

    protected $_connectionName  = null;
    protected $_connection      = null;
    protected $_authId = null;

    protected $_expire = 0;

    public function __construct($authId, string $connectionName = null) {
        $this->_authId          = $authId;
        $this->_connectionName  = $connectionName ?: static::$_defaultConnectionName;
    }

    public static function saveAll(): void {
        foreach (self::$_instances as $authId => $session) {
            $session->save();
        }
    }

    public function load(): self {
        $data = $this->getConnection()->hGetAll($this->getStoreKey());
        $data && $this->clear()->fill($data)->releaseOriginalData();
        return $this;
    }

    public function save(): self {
        [$updated, $removed, $newData]  = $this->getChanges();

        $connection = $this->getConnection();
        $storeKey   = $this->getStoreKey();
        if ($newData !== null) {
            $connection->del($storeKey);
            $connection->hMSet($storeKey, $newData);
        } else {
            $connection->hDel($storeKey, ...$removed);
            $connection->hMSet($storeKey, $updated);
        }
        $this->_expire && $connection->expire($storeKey, $this->_expire);
        return $this;
    }

    public function setExpire(int $expire): self {
        $this->_expire = $expire;
        return $this;
    }

    protected function getConnection() {
        if (!$this->_connection) {
            $this->_connection = rt::core()->redis($this->_connectionName);
        }
        return $this->_connection;
    }

    protected function getStoreKey() {
        if (!$this->_authId) {
            throw new \RuntimeException("Session has not auth id.");
        }
        return "combi:session:$this->_authId";
    }
}
