<?php
require __DIR__ . '/vendor/autoload.php';

// fix old symfony WebTest
$hasNewPhpUnit = class_exists('\PHPUnit\Framework\TestCase');
$hasOldPhpUnit = class_exists('\PHPUnit_Framework_TestCase');

if (! $hasOldPhpUnit && $hasNewPhpUnit) {

    class PHPUnit_Framework_TestCase extends \PHPUnit\Framework\TestCase
    {
    }
}
