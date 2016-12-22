<?php

namespace Combi\Utils;
use Symfony\Component\Translation\Util\ArrayConverter;

class ArrayCover
{
    private $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function __invoke(...$covers): array {
        foreach ($covers as $cover) {
            $this->data = $this->cover($this->data, $cover);
            $this->data = $this->cover($cover, $this->data);
        }
        return $this->data;
    }

    private function cover(array &$source, array &$cover): array {
        foreach ($cover as $key => $value) {
            if (is_array($value)) {
                (!isset($source[$key]) || !is_array($source[$key])) &&
                    $source[$key] = [];
                $this->cover($source[$key], $value);
            } else {
                $source[$key] = $value;
            }
        }
        return $source;
    }
}
