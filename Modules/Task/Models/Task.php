<?php

namespace Modules\Task\Models;

use Modules\Core\Models\CoreModel;
use Modules\Core\Traits\Model\Playable;
use Modules\User\Models\User;
use Propaganistas\LaravelFakeId\FakeIdTrait;
use Rinvex\Cacheable\CacheableEloquent;

class Task extends CoreModel
{
    // maintains created_by, updated_by and deleted_by
    use Playable;

    // automatic fake model id
    use FakeIdTrait;

    // cache queries on the model
    use CacheableEloquent;

    protected $fillable = [
        'user_id',
        'description',
        'file',
        'completed',
    ];

    protected $rules = [
        'description' => 'required',
    ];

    ###################################################################
    # RELATIONSHIPS START
    ###################################################################

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    ###################################################################
    # RELATIONSHIPS END
    ###################################################################

    ###################################################################
    # SCOPES START
    ###################################################################

    public function scopeCompleted($query)
    {
        return $query->where('completed', 1);
    }

    public function scopeUncompleted($query)
    {
        return $query->where('completed', 1);
    }

    ###################################################################
    # SCOPES END
    ###################################################################

    ###################################################################
    # ACCESSROS START
    ###################################################################

    public function getCompletedAttribute($value)
    {
        return $value == 1 ? 'Yes' : 'No';
    }

    ###################################################################
    # ACCESSROS END
    ###################################################################

    ###################################################################
    # MUTATORS START
    ###################################################################

    //

    ###################################################################
    # MUTATORS END
    ###################################################################

    ###################################################################
    # GENERAL FUNCTIONS START
    ###################################################################

    //

    ###################################################################
    # GENERAL FUNCTIONS END
    ###################################################################
}
