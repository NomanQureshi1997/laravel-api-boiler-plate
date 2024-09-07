<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;

class Contract extends Model
{
    use SoftDeletes;

    // Use UUID as the primary key
    protected $keyType = 'string';
    public $incrementing = false;

    // Fillable attributes
    protected $fillable = [
        'title', 'description', 'start_date', 'end_date',
    ];

    // Boot method to generate UUIDs
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

    // Define the relationship with users
    public function users()
    {
        return $this->belongsToMany(User::class, 'contract_user');
    }
}
