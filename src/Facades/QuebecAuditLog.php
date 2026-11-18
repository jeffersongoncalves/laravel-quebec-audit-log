<?php

namespace JeffersonGoncalves\QuebecAuditLog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\QuebecAuditLog\QuebecAuditLog
 */
class QuebecAuditLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-quebec-audit-log';
    }
}
