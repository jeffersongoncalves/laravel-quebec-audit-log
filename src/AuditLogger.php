<?php

namespace JeffersonGoncalves\QuebecAuditLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use JeffersonGoncalves\QuebecAuditLog\Models\AuditLog;
use RuntimeException;

class AuditLogger
{
    /**
     * Append an entry: who (the authenticated user unless given) did what to whose personal data.
     *
     * @param  list<string>  $columns  column names only, never values
     * @param  array<string, mixed>  $metadata
     */
    public function record(string $action, ?Model $subject = null, array $columns = [], ?string $purpose = null, array $metadata = [], ?Model $actor = null): AuditLog
    {
        $actor ??= $this->currentUser();
        $request = config('quebec-audit-log.store_request', true) && ! app()->runningInConsole() ? request() : null;

        return DB::transaction(function () use ($action, $subject, $columns, $purpose, $metadata, $actor, $request): AuditLog {
            // ponytail: locks the last row so concurrent writers chain one after another;
            // two writers on an empty table can still both start the chain (verify() reports it).
            $previous = AuditLog::query()->latest('id')->lockForUpdate()->value('hash');

            $log = new AuditLog([
                'actor_type' => $actor?->getMorphClass(),
                'actor_id' => $actor === null ? null : (string) $actor->getKey(),
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject === null ? null : (string) $subject->getKey(),
                'columns' => $columns === [] ? null : $columns,
                'purpose' => $purpose,
                'metadata' => $metadata === [] ? null : $metadata,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'created_at' => now()->startOfSecond(),
                'previous_hash' => $previous,
            ]);

            $log->hash = $this->hash($log);
            $log->save();

            return $log;
        });
    }

    /**
     * Walk the chain and return the id of the first entry that was altered, removed from
     * the middle or inserted out of order; null when the whole log is intact.
     * The oldest kept entry is trusted as the anchor, so pruning old entries keeps the rest verifiable.
     */
    public function verify(): ?int
    {
        $previous = false;

        foreach (AuditLog::query()->lazyById(500) as $log) {
            if (($previous !== false && $log->previous_hash !== $previous) || ! hash_equals($this->hash($log), $log->hash)) {
                return (int) $log->getKey();
            }

            $previous = $log->hash;
        }

        return null;
    }

    public function hash(AuditLog $log): string
    {
        $key = (string) (config('quebec.hash_key') ?: config('app.key'));

        if ($key === '') {
            throw new RuntimeException('Set quebec.hash_key (QUEBEC_HASH_KEY) or app.key to chain the audit log.');
        }

        return hash_hmac('sha256', (string) json_encode($log->chainPayload(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $key);
    }

    protected function currentUser(): ?Model
    {
        $user = auth()->user();

        return $user instanceof Model ? $user : null;
    }
}
