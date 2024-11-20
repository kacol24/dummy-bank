<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AccountType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'is_default',
        'is_locked',
        'name',
        'interest_rate',
        'period',
        'period_unit',
    ];

    public function dropdownDisplay(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $name = $attributes['name'];
                $interestRate = $attributes['interest_rate'];
                $period = $attributes['period'];
                $periodUnit = $attributes['period_unit'];
                $locked = $attributes['is_locked'] ? 'Locked:' : '';

                $string = "[$name] $interestRate% p.a";
                if ($attributes['is_locked']) {
                    $string .= " ($locked $this->friendly_period)";
                }

                return $string;
            }
        );
    }

    public function getFriendlyPeriodAttribute()
    {
        $suffix = $this->attributes['period_unit'];
        if ($this->attributes['period'] == 1) {
            $suffix = Str::singular($this->attributes['period_unit']);
        }

        return $this->attributes['period'].' '.$suffix;
    }
}
