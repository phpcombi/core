<?php
namespace Test\Playground;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/classes/Model.php';

class Package extends \Combi\Core\Package {
    public function bootstrap(): bool {
        return true;
    }
}
rt()->register(Package::class);

rt()->test_playground->container()->abc = function() {
    return 'xxvvff';
};
rt()->test_playground->container()->def = new class {
    public function __invoke() {
        return 'wwwffff';
    }
};
var_dump(rt()->test_playground->container()->abc);
var_dump(rt()->test_playground->container()->def);

\Tester\Assert::equal(1, 1);

die(1);


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

// exit with signal 1 to show echo message
die(1);
