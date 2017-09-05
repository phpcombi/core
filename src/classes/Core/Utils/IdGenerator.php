<?php

namespace Combi\Core\Utils;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 * Description of IdGenerator
 *
 * @author andares
 */
class IdGenerator {
    /**
     * use hash_algos() get list.
     *
     * @var string
     */
    private $algo;

    private $raw;
    private $data;

    public function __construct(string $algo = 'tiger128,3') {
        $this->algo = $algo;
    }

    public function prepare(string $data): self {
        $this->data     = $this->raw = $data;
        return $this;
    }

    public function get() {
        return $this->data;
    }

    public function hash_hmac(string $secret = null, $return_raw = false): self {
        $this->data = hash_hmac($this->algo, $this->data, $secret, $return_raw);
        return $this;
    }

    public function random_hex(int $length = 8): self {
        $this->data = bin2hex(random_bytes($length));
        return $this;
    }

    public function rand(int $min, int $max): self {
        $this->data = mt_rand($min, $max);
        return $this;
    }

    public function randByLength(int $length): self {
        return $this->rand(10 ** $length, (10 ** ($length + 1)) - 1);
    }

    public function length(int $length): self {
        $this->data = substr($this->data, 0, $length);
        return $this;
    }

    public function orderable(): self {
        $this->data = intval(microtime(true) * 1000).$this->data;
        return $this;
    }

    public function to62(): self {
        $value  = $this->data;
        $result = '';
        do {
            // 精度问题
            $value < 10000 && $value = intval($value);

            $last    = $value % 62;
            $value   -= $last;
            $value && $value /= 62;

            $ord     = $last < 10 ? (48 + $last)
                : ($last > 35 ? (61 + $last) : (55 + $last));
            $result .= chr($ord);
        } while ($value > 0);
        $this->data = strrev($result);
        return $this;
    }

    public function gmp_strval($bit = 62, $prefix = ''): self {
        $data = $prefix.$this->data;
        $gmp  = gmp_init($data);
        $this->data = gmp_strval($gmp, $bit);
        return $this;
    }

    public function base64(): self {
        $this->data = base64_encode($this->data);
        return $this;
    }

    public function urlencode(): self {
        $this->data = urlencode($this->data);
        return $this;
    }

    public function pack(string $pattern): self {
        $this->data = pack($pattern, $this->data);
        return $this;
    }

    public function strtoupper(): self {
        $this->data = strtoupper($this->data);
        return $this;
    }

    public function strtolower(): self {
        $this->data = strtolower($this->data);
        return $this;
    }

    public function base_convert(int $from, int $to): self {
        $this->data = base_convert($this->data, $from, $to);
        return $this;
    }

    public function uuid($trim = true) {
        $id = uuid_create();
        $this->data = $trim ? strtr($id, '-', '') : $id;
        return $this;
    }
}
