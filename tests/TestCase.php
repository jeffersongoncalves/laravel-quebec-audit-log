<?php

namespace JeffersonGoncalves\QuebecAuditLog\Tests;

use JeffersonGoncalves\QuebecAuditLog\QuebecAuditLogServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            QuebecAuditLogServiceProvider::class,
        ];
    }
}
