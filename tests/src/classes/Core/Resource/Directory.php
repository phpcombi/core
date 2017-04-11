<?php
namespace Combi\Core\Resource;

$_root_ = realpath(__DIR__ . '/../../../..');

require "$_root_/tests/bootstrap.php";

$dir = Directory::instance($_root_);

foreach ($dir as $fileinfo) {
    var_dump("$fileinfo");
}

$content = $dir->read('README.md');
var_dump($content);

$dir->replace('README.md', "$_root_/LICENSE");
$content = $dir->read('README.md');
var_dump($content);

var_dump($dir->exists('abc.php'));
var_dump($dir->exists('vendor/autoload.php'));

die(1);
