<?php

namespace JeffersonGoncalves\QuebecAuditLog\Models;

use JeffersonGoncalves\Privacy\AuditLog\AuditLog as BaseAuditLog;
use JeffersonGoncalves\Quebec\Attributes\PersonalData;
use JeffersonGoncalves\Quebec\Concerns\InteractsWithPersonalData;
use JeffersonGoncalves\Quebec\Contracts\HasPersonalData;

/**
 * One audit entry. Append-only and hash-chained: only the request columns (personal data,
 * outside the chain) may change, so an actor can still be anonymized.
 */
#[PersonalData('ip_address', purpose: 'Accountability for the protection of personal information (s. 3.1)')]
#[PersonalData('user_agent', purpose: 'Accountability for the protection of personal information (s. 3.1)')]
class AuditLog extends BaseAuditLog implements HasPersonalData
{
    use InteractsWithPersonalData;

    public function getTable(): string
    {
        return config('quebec-audit-log.table', 'quebec_audit_logs');
    }
}
