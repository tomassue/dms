<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // CRITICAL FIX: Explicitly exclude the Livewire upload route
        'livewire/upload-file',
        'livewire/upload-file/*', 
    ];
}