<?php

namespace JeffersonGoncalves\QuebecAuditLog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\Quebec\Attributes\PersonalData;
use JeffersonGoncalves\Quebec\Concerns\InteractsWithPersonalData;
use JeffersonGoncalves\Quebec\Contracts\HasPersonalData;
use LogicException;

/**
 * One audit entry. Append-only and hash-chained: only the request columns (personal data,
 * outside the chain) may change, so an actor can still be anonymized.
 *
 * @property string $action
 * @property list<string>|null $columns
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 * @property string|null $previous_hash
 * @property string $hash
 */
#[PersonalData('ip_address', purpose: 'Accountability for the protection of personal information (s. 3.1)')]
#[PersonalData('user_agent', purpose: 'Accountability for the protection of personal information (s. 3.1)')]
class AuditLog extends Model implements HasPersonalData
{
    use InteractsWithPersonalData;

    public const UPDATED_AT = null;

    protected $guarded = [];

    public function getTable(): string
    {
        return config('quebec-audit-log.table', 'quebec_audit_logs');
    }

    protected function casts(): array
    {
        return [
            'columns' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (AuditLog $log): void {
            if (array_diff(array_keys($log->getDirty()), ['ip_address', 'user_agent']) !== []) {
                throw new LogicException('Audit logs are append-only.');
            }
        });

        static::deleting(function (): void {
            throw new LogicException('Audit logs are append-only and cannot be deleted.');
        });
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The chained fields, in a fixed order. IP address and user agent are left out on purpose.
     *
     * @return list<mixed>
     */
    public function chainPayload(): array
    {
        return [
            $this->previous_hash,
            $this->getAttribute('actor_type'),
            $this->getAttribute('actor_id'),
            $this->action,
            $this->getAttribute('subject_type'),
            $this->getAttribute('subject_id'),
            $this->columns,
            $this->getAttribute('purpose'),
            $this->metadata,
            $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
