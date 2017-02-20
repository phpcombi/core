<?php

namespace Combi\Utils;

/**
 * 数组合并功能类。
 *
 * 可将两个任意深度的数组完全合并到一起。
 */
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
            } elseif ($value !== null) {
                $source[$key] = $value;
            }
        }
        return $source;
    }
}
