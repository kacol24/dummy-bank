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
        'account_type_id',
        'is_primary',
        'account_number',
        'name',
    ];

    protected $with = [
        'wallet',
    ];

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
        });

        static::created(function (Account $account) {
            if ($account->accountType->is_default) {
                $account->timeDeposit()->create([
                    'interest_rate' => $account->accountType->interest_rate,
                    'period'        => $account->accountType->period,
                    'period_unit'   => $account->accountType->period_unit,
                ]);
            }
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

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function timeDeposit()
    {
        return $this->hasOne(TimeDeposit::class);
    }
}
