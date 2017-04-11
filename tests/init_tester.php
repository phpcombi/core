<?php

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .
if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

// configure environment
Tester\Environment::setup();
$_GET = $_POST = $_COOKIE = [];

// create temporary directory
@mkdir(dirname(TEMP_DIR)); // @ - directory may already exist
Tester\Helpers::purge(TEMP_DIR);

// disable xdebug
if (extension_loaded('xdebug')) {
	xdebug_disable();
}

