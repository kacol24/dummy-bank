<?php

namespace App\Models;

use HPWebdeveloper\LaravelPayPocket\Interfaces\WalletOperations;
use HPWebdeveloper\LaravelPayPocket\Traits\ManagesWallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model implements WalletOperations
{
    use SoftDeletes;
    use ManagesWallet;

    protected $fillable = [
        'user_id',
        'account_number',
        'name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
