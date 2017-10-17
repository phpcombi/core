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
 * 设计 token key 长度<32字节，有效索引16字节； packed key 长度<50字节。
 *
 * expire 单位为分钟
 *
 * @author andares
 */
abstract class Token
{
    protected static $keySecret     = 'lEgAcY034';
    protected static $keyExpire     = 43200;
    protected static $codeSecret    = 'undefined';

    protected static $refreshKeyExpire  = 86400;

    protected $id;
    protected $key;
    protected $expiration;

    protected $refreshKey;
    protected $refreshExpiration;
    protected $previousKey = null;

    public static function withPackedKey(string $packedKey, bool $autoLoad = true): self {
        $token = new static();
        [$token->key, $token->expiration, $code] = static::unpack($packedKey);
        if (!$token->isValid($code)) {
            throw new \RuntimeException("token is not valid.");
        }
        if ($token->isExpired()) {
            throw new \RuntimeException("token is expired.");
        }
        $autoLoad && $token->load();
        return $token;
    }

    public static function withId($id): self {
        $token = new static();
        $token->id = $id;
        $token->genKey()->setExpire()
            ->genRefreshKey()->setRefreshExpire();
        return $token;
    }

    public function isValid(string $code): bool {
        return $code == $this->getCode($this->key, $this->expiration);
    }

    public function isExpired(int $time = null): bool {
        !$time && $time = rt::core()->now->getTimestamp();
        return $time >= $this->getExpiration();
    }

    public function refresh($force = false): self {
        if (!$force && !$this->isExpired()) {
            return $this;
        }

        $token = static::withId($this->id);
        $token->previousKey = $this->getKey();
        return $token;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function getId() {
        return $this->id;
    }

    public function getExpiration(): int {
        return $this->expiration * 60;
    }

    public function getRefreshKey(): string {
        return $this->refreshKey;
    }

    public function getRefreshExpiration(): int {
        return $this->refreshExpiration * 60;
    }

    public function getPreviousKey(): ?string {
        return $this->previousKey;
    }

    public function toArray(): array {
        return [
            'id'                => $this->getId(),
            'key'               => $this->getKey(),
            'expiration'        => $this->getExpiration(),
            'refreshKey'        => $this->getRefreshKey(),
            'refreshExpiration' => $this->getRefreshExpiration(),
            'previousKey'       => $this->getPreviousKey(),
        ];
    }

    public function setExpire($expire = null): self {
        !$expire && $expire = static::$keyExpire;
        $this->expiration   = ceil(rt::core()->now->getTimestamp() / 60) + $expire;
        return $this;
    }

    public function setRefreshExpire($expire = null): self {
        !$expire && $expire = static::$refreshKeyExpire;
        $this->refreshExpiration   = ceil(rt::core()->now->getTimestamp() / 60) + $expire;
        return $this;
    }

    public function __toString(): string {
        return $this->pack();
    }

    public function load() {
        $redis  = rt::core()->redis();
        $data   = msgpack_unpack($redis->get(":token:$this->key"));
        [
            $this->id,
            $this->key,
            $this->expiration,
            $this->refreshKey,
            $this->refreshExpiration,
            $this->previousKey,
        ] = $data;
    }

    public function save() {
        $data   = msgpack_pack([
            $this->id,
            $this->key,
            $this->expiration,
            $this->refreshKey,
            $this->refreshExpiration,
            $this->previousKey,
        ]);
        $redis  = rt::core()->redis();
        $redis->setEx(":token:$this->key", static::$refreshKeyExpire * 60, $data);
    }

    /**
     *
     * @param string $packedKey
     * @return array
     */
    protected static function unpack(string $packedKey): array {
        $result = explode('-', $packedKey);

        $gen = new Core\Utils\IdGenerator();
        $raw = $result[1];
        $result[1] = $gen->prepare($result[1])->gmpIntval(62)->get();
        return $result;
    }

    /**
     *
     * @return string
     */
    protected function pack(): string {
        $gen    = new Core\Utils\IdGenerator();
        $expiration = $gen->prepare($this->expiration)->gmpStrval()->get();
        return "$this->key-$expiration-".$this->getCode($this->key, $this->expiration);
    }

    /**
     *
     * @return static
     */
    protected function genKey(): self {
        $gen = new Core\Utils\IdGenerator();
        $prefix = $gen->prepare()->orderable(1000)->gmpStrval(62)->get();

        $gen = new Core\Utils\IdGenerator();
        $this->key = $prefix.$gen->prepare(substr(serialize($this->id), 0, 32))
            ->hashHmac(static::$keySecret.microtime())
            ->gmpStrval(62, '0x')
            ->get();
        return $this;
    }

    /**
     *
     * @param string $key
     * @param int $expiration
     * @return string
     */
    protected function getCode(string $key, int $expiration): string {
        $gen    = new Core\Utils\IdGenerator();
        $data   = md5($this->key.static::$codeSecret.$expiration);
        return substr($gen->prepare($this->key.static::$codeSecret.$expiration)
            ->md5()
            ->gmpStrval(62, '0x')
            ->get(), -10);
    }

    protected function genRefreshKey(): self {
        $gen = new Core\Utils\IdGenerator();
        $this->refreshKey = $gen->prepare(random_bytes(8))
            ->hashHmac($this->key)
            ->gmpStrval(62, '0x')
            ->get();
        return $this;
    }

}
