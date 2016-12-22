<?php
namespace Test\Playground;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/classes/Model.php';

\Tester\Assert::equal(1, 1);

// 以下是测meta的

$person = new Person();
// overloaded
$person->name    = 'andares';
$person->father  = 'unamed';
$person->age     = 34;

// arrayAccess
\Tester\Assert::equal(34, $person['age']);

// iterator
foreach ($person as $key => $value) {
    var_dump("$key = $value");
}

// fillable & confirm
$person->exclude('name')->fill([
    'name'      => 'good',
    'father'    => 'www',
    'age'       => 40,
])->confirm();

// toArray
var_dump($person->toArray());

// jsonserialize
var_dump(json_encode($person));
var_dump("$person");

// tobin
$bin = $person->toBin();
var_dump($bin);

// serializable
$packed = serialize($person);
var_dump($packed);
var_dump(unserialize($packed));

$person->child = new Person();
$person->child->name = 'max';
$person->confirm();
var_dump("=============================");
$packed = serialize($person);
var_dump($packed);
var_dump(unserialize($packed));

// exit with signal 1 to show echo message
die(1);
