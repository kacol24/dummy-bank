<?php

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model implements Wallet
{
    use SoftDeletes;
    use HasWallet;

    protected $fillable = [
        'user_id',
        'account_number',
        'name',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = auth()->id();
        });
    }

    public function dropdownDisplay(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => "[{$attributes['account_number']}] {$attributes['name']}"
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
