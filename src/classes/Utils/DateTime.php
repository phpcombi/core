<?php

namespace Combi\Utils;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

/**
 * 时间类。
 * 管理时区，格式化时间，now功能（带自动刷新）
 */
class DateTime
{
    private static $units_index = [
        'year'      => 'Y',
        'month'     => 'M',
        'week'      => 'W',
        'day'       => 'D',
        'hour'      => 'H',
        'minute'    => 'M',
        'second'    => 'S',
    ];

    protected $timezone;
    protected $datetime;

    public function __consturct($time = 'now', ?\DateTimeZone $timezone = null) {
        $this->timezone = $timezone
            ?: new \DateTimeZone(\date_default_timezone_get() ?: 'UTC');
        $this->datetime = new \DateTime($time, $this->timezone);
    }

    public function to(string $time,
        int $interval, string $unit = 'day'): \DatePeriod
    {
        $to = new \DateTime($time, $this->timezone);
        in_array($unit, ['hour', 'minute', 'second'])
            ? $to->modify("+$interval $unit")
            : $to->modify("+$interval day");

        return $this->between($this->datetime, $to,
            "P$interval" . self::$units_index[$unit]);
    }

    public function from(string $time,
        int $interval, string $unit = 'day'): \DatePeriod
    {
        $from   = new \DateTime($time, $this->timezone);
        $to     = clone $this->datetime;
        in_array($unit, ['hour', 'minute', 'second'])
            ? $to->modify("+1 $unit")
            : $to->modify("+1 day");

        return $this->between($from, $to,
            "P$interval" . self::$units_index[$unit]);
    }

    public function between(\DateTimeInterface $begin,
        \DateTimeInterface $end, int $interval): \DatePeriod
    {
        $interval = new \DateInterval($interval);
        return new \DatePeriod($begin, $interval, $end);
    }

    public function micro(): float {
        return microtime(true);
    }

    public function now(): \DateTimeImmutable {
        return new \DateTimeImmutable('now', $this->timezone);
    }

    public function diff($datetime2, $absolute = null): \DateInterval
    {
        return $this->datetime->diff($datetime2, $absolute);
    }

    public function format($format): string {
        return $this->datetime->format($format);
    }

    public function getOffset(): int {
        return $this->datetime->getOffset();
    }

    public function getTimestamp(): int {
        return $this->datetime->getTimestamp();
    }

    public function getTimezone(): \DateTimeZone {
        return $this->timezone;
    }

    public function __wakeup() {
        // do nothing
    }

    public function __call(string $name, array $arguments) {
        return $this->datetime->$name(...$arguments);
    }
}
