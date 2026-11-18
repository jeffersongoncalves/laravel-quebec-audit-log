<?php

namespace JeffersonGoncalves\QuebecAuditLog;

use Illuminate\Support\Facades\Event;
use JeffersonGoncalves\Quebec\Events\PersonalDataAnonymized;
use JeffersonGoncalves\QuebecAuditLog\Commands\VerifyCommand;
use JeffersonGoncalves\QuebecAuditLog\Listeners\LogAnonymization;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QuebecAuditLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('quebec-audit-log')
            ->hasConfigFile()
            ->hasMigrations(['create_quebec_audit_logs_table'])
            ->hasCommand(VerifyCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AuditLogger::class);
    }

    public function packageBooted(): void
    {
        if (config('quebec-audit-log.log_anonymizations', true)) {
            Event::listen(PersonalDataAnonymized::class, LogAnonymization::class);
        }
    }
}
