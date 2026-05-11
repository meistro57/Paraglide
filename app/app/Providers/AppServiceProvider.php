<?php

namespace App\Providers;

use App\Services\Crypto\Encryptor;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Encryptor::class, fn () => new Encryptor());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event): void {
            if (! $event->connection->getConfig('sqlcipher')) {
                return;
            }

            $keyHex = $event->connection->getConfig('sqlcipher_key');

            if (! is_string($keyHex) || $keyHex === '' || ! ctype_xdigit($keyHex)) {
                return;
            }

            $event->connection->unprepared(sprintf("PRAGMA key = \"x'%s'\"", $keyHex));
        });
    }
}
