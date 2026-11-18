<?php

namespace JeffersonGoncalves\QuebecAuditLog\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\PestPluginDatabaseMigrations\LoadsPackageMigrations;
use JeffersonGoncalves\Quebec\QuebecServiceProvider;
use JeffersonGoncalves\QuebecAuditLog\QuebecAuditLogServiceProvider;
use JeffersonGoncalves\QuebecAuditLog\Tests\Fixtures\Customer;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use LoadsPackageMigrations;

    protected function getPackageProviders($app): array
    {
        return [
            QuebecServiceProvider::class,
            QuebecAuditLogServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('quebec.models', [Customer::class]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadPackageMigrations(QuebecAuditLogServiceProvider::class);

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('plan')->nullable();
        });
    }
}
