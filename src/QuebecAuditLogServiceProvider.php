<?php

namespace JeffersonGoncalves\QuebecAuditLog;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QuebecAuditLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-quebec-audit-log')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
