<?php

namespace JeffersonGoncalves\QuebecAuditLog\Concerns;

use JeffersonGoncalves\Privacy\AuditLog\AuditLogger as BaseAuditLogger;
use JeffersonGoncalves\Privacy\AuditLog\Concerns\AuditsPersonalData as BaseAuditsPersonalData;
use JeffersonGoncalves\QuebecAuditLog\AuditLogger;

/**
 * For HasPersonalData models: logs changes to declared personal data columns (names only)
 * and deletions. Reads can't be detected by Eloquent: call auditAccess() where you show the data.
 */
trait AuditsPersonalData
{
    use BaseAuditsPersonalData;

    protected static function auditLogger(): BaseAuditLogger
    {
        return app(AuditLogger::class);
    }
}
