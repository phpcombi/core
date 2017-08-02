<?php

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

use Tester\Assert;

require __DIR__ . '/bootstrap.php';

\Tester\Assert::equal(1, 1);

// require __DIR__ . '/playground/middleware.php';

// helper::du(core::config('settings')->toArray());

// die(1);

helper::timer();



$aa = new class extends \Combi\Core\Action {
    use core\Traits\LoggerInject;

    protected function handle() {
        helper::du('a:'.core::now()->format('Y-m-d H:i:s'));
        $this->error('aabbcc');
    }
};

$bb = new class extends \Combi\Core\Action {
    protected function handle() {
        helper::du('b:'.core::now()->format('Y-m-d H:i:s'));
    }
};

$cc = new class extends \Combi\Core\Action {
    protected function handle() {
        helper::du('c:'.core::now()->format('Y-m-d H:i:s'));
        sleep(1);
    }
};

helper::du($aa->getActionId());
helper::du($bb->getActionId());
helper::du($cc->getActionId());

// helper::warning($bbddww);

// helper::warning('vvvvv', ['xxxxx', 'bbb' => core::instance()]);

// helper::logger('debug')->warning('vvvvv', ['xxxxx', 'bbb' => core::instance()]);
// helper::logger('debug')->warning('vvvvv', ['xxxxx', 'bbb' => core::instance()]);
// helper::logger('slow')->info('vvvvvvdddddd');
// helper::logger('slow')->info('vvvvvvdddddd');

// helper::warning('vvvvv', ['xxxxx', 'bbb' => core::instance()]);

// throw abort::runtime('aaa bbbb %qqq%:eee')->set('qqq', 'andares');

helper::log(abort::runtime('aaa bbbb %qqq%:eee')->set('qqq', 'andares'));
// helper::warning(abort::runtime('aaa bbbb %qqq%:eee')->set('qqq', 'andares'));

$a = helper::timer();
helper::du($a);

$cc();
$bb();
$aa();


// exit with signal 1 to show echo message/**/
die(1);
