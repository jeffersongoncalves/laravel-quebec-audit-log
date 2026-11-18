<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use JeffersonGoncalves\QuebecAuditLog\Facades\QuebecAuditLog;
use JeffersonGoncalves\QuebecAuditLog\Models\AuditLog;
use JeffersonGoncalves\QuebecAuditLog\Tests\Fixtures\Customer;

function customer(): Customer
{
    return Customer::create(['name' => 'Mary', 'email' => 'mary@example.com', 'plan' => 'free']);
}

it('records who did what to whose data, with column names only', function () {
    $customer = customer();
    $agent = Customer::create(['name' => 'Agent']);

    $log = QuebecAuditLog::record('exported', $customer, ['email'], 'Answer a DSAR', ['ticket' => 42], actor: $agent);

    expect($log->fresh())
        ->action->toBe('exported')
        ->subject->is($customer)->toBeTrue()
        ->actor->is($agent)->toBeTrue()
        ->columns->toBe(['email'])
        ->purpose->toBe('Answer a DSAR')
        ->metadata->toBe(['ticket' => 42]);
});

it('logs changes to personal data, deletions and explicit access', function () {
    $customer = customer();

    $customer->update(['plan' => 'pro']); // not personal data: no entry
    $customer->update(['email' => 'new@example.com', 'plan' => 'free']);
    $customer->auditAccess('Support ticket');
    $customer->delete();

    expect(AuditLog::query()->orderBy('id')->get(['action', 'columns'])->toArray())->toBe([
        ['action' => 'updated', 'columns' => ['email']],
        ['action' => 'viewed', 'columns' => ['name', 'email']],
        ['action' => 'deleted', 'columns' => null],
    ])->and(json_encode(AuditLog::all()))->not->toContain('new@example.com');
});

it('logs every anonymization', function () {
    customer()->anonymizePersonalData();

    // The save is an update of personal data too; the anonymized entry follows it.
    expect(AuditLog::query()->orderBy('id')->pluck('action')->all())->toBe(['updated', 'anonymized'])
        ->and(AuditLog::query()->latest('id')->first()->columns)->toBe(['name', 'email']);
});

it('chains entries and detects tampering', function () {
    $customer = customer();
    foreach (['viewed', 'exported', 'viewed'] as $action) {
        QuebecAuditLog::record($action, $customer);
    }

    expect(QuebecAuditLog::verify())->toBeNull();

    $second = AuditLog::query()->orderBy('id')->skip(1)->first();
    DB::table('quebec_audit_logs')->where('id', $second->id)->update(['action' => 'viewed']);

    expect(QuebecAuditLog::verify())->toBe($second->id);
    Artisan::call('quebec:audit-verify');
    expect(Artisan::output())->toContain("breaks at entry #{$second->id}");
});

it('detects an entry removed from the middle, but accepts pruned history', function () {
    $customer = customer();
    foreach (range(1, 4) as $i) {
        QuebecAuditLog::record('viewed', $customer);
    }
    $ids = AuditLog::query()->orderBy('id')->pluck('id');

    DB::table('quebec_audit_logs')->where('id', $ids[0])->delete(); // oldest pruned: fine
    expect(QuebecAuditLog::verify())->toBeNull();

    DB::table('quebec_audit_logs')->where('id', $ids[2])->delete(); // a gap: broken at the next one
    expect(QuebecAuditLog::verify())->toBe($ids[3]);
});

it('is append-only but lets the request data be anonymized', function () {
    $log = QuebecAuditLog::record('viewed', customer());

    expect(fn () => $log->update(['action' => 'nothing']))->toThrow(LogicException::class)
        ->and(fn () => $log->delete())->toThrow(LogicException::class);

    $log = $log->fresh();
    $log->forceFill(['ip_address' => '10.0.0.1'])->save();
    $log->anonymizePersonalData();

    expect(QuebecAuditLog::verify())->toBeNull()
        ->and($log->fresh()->ip_address)->toBeNull();
});
