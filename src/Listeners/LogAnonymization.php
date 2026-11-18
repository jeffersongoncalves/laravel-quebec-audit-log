<?php

namespace JeffersonGoncalves\QuebecAuditLog\Listeners;

use JeffersonGoncalves\Quebec\Events\PersonalDataAnonymized;
use JeffersonGoncalves\QuebecAuditLog\AuditLogger;

class LogAnonymization
{
    public function __construct(protected AuditLogger $logger) {}

    public function handle(PersonalDataAnonymized $event): void
    {
        $this->logger->record('anonymized', $event->model, $event->columns);
    }
}
