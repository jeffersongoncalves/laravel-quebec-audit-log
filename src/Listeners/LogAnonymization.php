<?php

namespace JeffersonGoncalves\QuebecAuditLog\Listeners;

use JeffersonGoncalves\Privacy\AuditLog\Listeners\LogAnonymization as BaseLogAnonymization;
use JeffersonGoncalves\QuebecAuditLog\AuditLogger;

class LogAnonymization extends BaseLogAnonymization
{
    public function __construct(AuditLogger $logger)
    {
        parent::__construct($logger);
    }
}
