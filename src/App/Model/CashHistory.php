<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CashHistory extends Model
{
    protected $table = "cash_history";
    public $timestamps = false;

    public $fillable = [
        "amount", "description", "clerk_id", "transaction_id", "datetime"
    ];
}