<?php

use App\Support\ServerlessRuntime;

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. On Vercel we fall back to the writable
    | temp filesystem so Blade compilation does not target the read-only build.
    |
    */

    'compiled' => env('VIEW_COMPILED_PATH', ServerlessRuntime::viewCompiledPath()),

];
