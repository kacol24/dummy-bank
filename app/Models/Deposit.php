<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Deposit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'interest_rate',
        'period',
        'period_unit',
        'ends_at',
        'rollover_instruction',
        'rollover_counter',
    ];

    public function friendlyPeriod(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes){
                if ($attributes['period'] == 1) {
                    return Str::singular($attributes['period_unit']);
                }

                return $attributes['period'] . ' ' . $attributes['period_unit'];
            }
        );
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
