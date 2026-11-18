<?php

namespace JeffersonGoncalves\QuebecAuditLog\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use JeffersonGoncalves\Quebec\Attributes\PersonalData;
use JeffersonGoncalves\Quebec\Concerns\InteractsWithPersonalData;
use JeffersonGoncalves\Quebec\Contracts\HasPersonalData;
use JeffersonGoncalves\Quebec\Enums\AnonymizeStrategy;
use JeffersonGoncalves\QuebecAuditLog\Concerns\AuditsPersonalData;

#[PersonalData('name', anonymize: AnonymizeStrategy::Mask)]
#[PersonalData('email', anonymize: AnonymizeStrategy::Hash)]
class Customer extends Model implements HasPersonalData
{
    use AuditsPersonalData;
    use InteractsWithPersonalData;

    protected $guarded = [];

    public $timestamps = false;
}
