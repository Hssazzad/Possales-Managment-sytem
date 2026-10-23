<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Boost Master Switch
    |--------------------------------------------------------------------------
    |
    | Disables all Laravel Boost functionality, including route registration
    | and the browser logger script injection (InjectBoost middleware).
    | Override per-environment via BOOST_ENABLED=true when AI tooling is needed.
    |
    */

    'enabled' => env('BOOST_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Boost Browser Logs Watcher
    |--------------------------------------------------------------------------
    |
    | Disables the browser console logger that prints:
    | "Browser logger active (MCP server detected). Posting to: /_boost/browser-logs"
    | and POSTs console output to the server. Override via
    | BOOST_BROWSER_LOGS_WATCHER=true only on local dev machines.
    |
    */

    'browser_logs_watcher' => env('BOOST_BROWSER_LOGS_WATCHER', false),

];
