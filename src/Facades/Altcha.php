<?php

namespace Climactic\Altcha\Altcha\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Climactic\Altcha\Altcha\Altcha
 */
class Altcha extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Climactic\Altcha\Altcha\Altcha::class;
    }
}
