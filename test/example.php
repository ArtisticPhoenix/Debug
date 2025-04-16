<?php
use \evo\debug\Debug;

/*
 * http://localhost/evo/Debug/test/example.php
 */
require __DIR__.'/../vendor/autoload.php';

Debug::I()->regesterFunctions();

final class example{
    const string MODE_READ = 'a+';
    public readonly string $foo_bar;
    public static string $static_biz = 'static_biz';
    protected static string $static_baz = 'static_baz';
    private static string $static_boz = 'static_boz';
    public string $biz = 'biz';
    protected string $baz = 'baz';
    private string $boz = 'boz';
}

$bar = new example();

echo "<pre>";
debug_dump($bar);