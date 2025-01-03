<?php

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model implements Wallet
{
    use SoftDeletes;
    use HasWallet;
    use HasSnowflakes;

    protected $fillable = [
        'user_id',
        'account_type_id',
        'is_primary',
        'account_number',
        'name',
    ];

    protected $with = [
        'wallet',
        'transactions',
    ];

    protected $appends = [
        'last_transaction',
    ];

    // conflict with wallet package
    //protected $casts = [
    //    'id' => Snowflake::class,
    //];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->user_id) {
                $model->user_id = auth()->id();
            }
            if (! $model->account_number) {
                $accountNumber = null;
                while (is_null($accountNumber)) {
                    $branchCode = '863';
                    $random = mt_rand(1000000, 9999999);
                    $candidate = $branchCode.$random;
                    if (Account::withTrashed()->where('account_number', $candidate)->doesntExist()) {
                        $accountNumber = $candidate;
                    }
                }
                $model->account_number = $accountNumber;
            }
            if (auth()->user()->accounts->count() == 0) {
                $model->is_primary = true;
            }
        });

        static::created(function (Account $account) {
            $accountType = $account->accountType;
            $account->timeDeposit()->create([
                'interest_rate' => $accountType->interest_rate,
                'period'        => $accountType->period,
                'period_unit'   => $accountType->period_unit,
                'ends_at'       => $accountType->is_locked ? today()->add("$accountType->period $accountType->period_unit") : null,
            ]);
        });
    }

    public function scopeOwner(Builder $query, $userId = null)
    {
        if (! $userId) {
            $userId = auth()->id();
        }

        return $query->where('user_id', $userId);
    }

    public function getLastTransactionAttribute()
    {
        return $this->transactions->last();
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

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function timeDeposit()
    {
        return $this->hasOne(TimeDeposit::class);
    }
}
