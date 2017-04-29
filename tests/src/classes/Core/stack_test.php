<?php

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Test\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Core\Business\Middleware\Stack;

$stack_a = Stack::instance('a');
$stack_b = Stack::instance('b');
$stack_c = Stack::instance('c');
$action  = function($params, $result) {
    tris::du("action is running");
    return $result;
};

$stack_a->append(function($params, $result, $next) {
    tris::du(">>>>>> stack_a in");
    $result = $next($params, $result);
    tris::du(">>>>>> stack_a out");
    return $result;
});
$stack_a->append(function($params, $result, $next) {
    tris::du(">>>> stack_a in");
    $result = $next($params, $result);
    tris::du(">>>> stack_a out");
    return $result;
});
$stack_a->append(function($params, $result, $next) {
    tris::du(">> stack_a in");
    $result = $next($params, $result);
    tris::du(">> stack_a out");
    return $result;
});

$stack_b->append(function($params, $result, $next) {
    tris::du(">>>>>> stack_b in");
    $result = $next($params, $result);
    tris::du(">>>>>> stack_b out");
    return $result;
});
$stack_b->append(function($params, $result, $next) {
    tris::du(">>>> stack_b in");
    $result = $next($params, $result);
    tris::du(">>>> stack_b out");
    return $result;
});
$stack_b->append(function($params, $result, $next) {
    tris::du(">> stack_b in");
    $result = $next($params, $result);
    tris::du(">> stack_b out");
    return $result;
});

$stack_c->append(function($params, $result, $next) {
    tris::du(">>>>>> stack_c in");
    $result = $next($params, $result);
    tris::du(">>>>>> stack_c out");
    return $result;
});
$stack_c->append(function($params, $result, $next) {
    tris::du(">>>> stack_c in");
    $result = $next($params, $result);
    tris::du(">>>> stack_c out");
    return $result;
});
$stack_c->append(function($params, $result, $next) {
    tris::du(">> stack_c in");
    $result = $next($params, $result);
    tris::du(">> stack_c out");
    return $result;
});

$stack_c
    ->kernel($stack_b)
    ->kernel($stack_a)
    ->kernel($action);

var_dump($stack_c(1, 2));
