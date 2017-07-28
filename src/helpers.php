
<?php

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

helper::register('gen_id', function(int $random_length = 3): string {
    return (new core\Utils\IdGenerator())
        ->random_hex($random_length)
        ->orderable()
        ->get();
});


helper::register('du', function($var, $title = null): void {
    static $count;
    $count++;
    !$title && $title = "Dump Count: $count";

    core\Utils\Debug::instance()->dump($var, $title);
});

helper::register('dd', function($var, $title = null): void {
    helper::du($var, $title);
    die(1);
});

helper::register('timer', function(string $name = 'default'): float {
    return core\Utils\Debug::instance()->timer($name);
});

