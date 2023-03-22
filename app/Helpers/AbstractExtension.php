<?php

namespace App\Helpers;

// create a abstract class for the extension that will contain all the methods that will be used in the extension
abstract class AbstractExtension
{
    abstract public static function getConfig(): array;
}
