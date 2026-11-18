<?php

namespace JeffersonGoncalves\QuebecAuditLog\Commands;

use JeffersonGoncalves\Privacy\AuditLog\Commands\VerifyCommand as BaseVerifyCommand;
use JeffersonGoncalves\QuebecAuditLog\AuditLogger;

class VerifyCommand extends BaseVerifyCommand
{
    protected $signature = 'quebec:audit-verify';

    protected $description = 'Check that the QUEBEC audit log was not altered';

    protected string $logger = AuditLogger::class;
}
