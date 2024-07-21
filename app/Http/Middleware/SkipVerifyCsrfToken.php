<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

class SkipVerifyCsrfToken extends VerifyCsrfToken
{
    protected $except = [
        '/livewire/*',
    ];
}
