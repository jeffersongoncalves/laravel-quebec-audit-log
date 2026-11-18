<?php

namespace JeffersonGoncalves\QuebecAuditLog;

use JeffersonGoncalves\Privacy\AuditLog\AuditLogger as BaseAuditLogger;
use JeffersonGoncalves\Quebec\Quebec;
use JeffersonGoncalves\QuebecAuditLog\Models\AuditLog;

/**
 * Append-only, hash-chained log of who did what to whose personal data.
 *
 * @method AuditLog record(string $action, ?\Illuminate\Database\Eloquent\Model $subject = null, list<string> $columns = [], ?string $purpose = null, array<string, mixed> $metadata = [], ?\Illuminate\Database\Eloquent\Model $actor = null)
 */
class AuditLogger extends BaseAuditLogger
{
    protected string $config = 'quebec-audit-log';

    protected string $model = AuditLog::class;

    public function __construct(Quebec $quebec)
    {
        parent::__construct($quebec);
    }
}
