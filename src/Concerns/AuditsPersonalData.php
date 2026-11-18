<?php

namespace JeffersonGoncalves\QuebecAuditLog\Concerns;

use JeffersonGoncalves\Quebec\Attributes\PersonalData;
use JeffersonGoncalves\QuebecAuditLog\AuditLogger;
use JeffersonGoncalves\QuebecAuditLog\Models\AuditLog;

/**
 * For HasPersonalData models: logs changes to declared personal data columns (names only)
 * and deletions. Reads can't be detected by Eloquent: call auditAccess() where you show the data.
 */
trait AuditsPersonalData
{
    public static function bootAuditsPersonalData(): void
    {
        static::updated(function (self $model): void {
            $columns = array_values(array_intersect(array_keys($model->getChanges()), $model->personalDataColumns()));

            if ($columns !== []) {
                app(AuditLogger::class)->record('updated', $model, $columns);
            }
        });

        static::deleted(function (self $model): void {
            app(AuditLogger::class)->record('deleted', $model);
        });
    }

    /**
     * @param  list<string>  $columns  the columns shown; all declared ones when empty
     */
    public function auditAccess(?string $purpose = null, array $columns = []): AuditLog
    {
        return app(AuditLogger::class)->record('viewed', $this, $columns === [] ? $this->personalDataColumns() : $columns, $purpose);
    }

    /**
     * @return list<string>
     */
    protected function personalDataColumns(): array
    {
        return array_map(fn (PersonalData $field): string => $field->column, static::personalDataFields());
    }
}
