<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Enable SQLite WAL mode for better concurrency
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA journal_mode=WAL;');
            DB::statement('PRAGMA synchronous=NORMAL;');
            DB::statement('PRAGMA cache_size=10000;');
        }
    }
}
