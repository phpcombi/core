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
protected function handle() {helper::du('a');}
};

$bb = new class extends \Combi\Core\Action {
protected function handle() {helper::du('b');}

};

$cc = new class extends \Combi\Core\Action {
protected function handle() {helper::du('c');}

};

helper::du($aa->getActionId());
helper::du($bb->getActionId());
helper::du($cc->getActionId());

// helper::warning($bbddww);

// helper::warning('vvvvv', ['xxxxx', 'bbb' => core::instance()]);

// helper::log('debug')->warning('vvvvv', ['xxxxx', 'bbb' => core::instance()]);
// helper::log('debug')->warning('vvvvv', ['xxxxx', 'bbb' => core::instance()]);
// helper::log('slow')->info('vvvvvvdddddd');
// helper::log('slow')->info('vvvvvvdddddd');

// helper::warning('vvvvv', ['xxxxx', 'bbb' => core::instance()]);

helper::error(abort::logic('aaa bbbb %qqq%:eee')->set('qqq', 'andares'));

$a = helper::timer();
helper::du($a);

$cc();
$bb();
$aa();


// exit with signal 1 to show echo message/**/
die(1);
