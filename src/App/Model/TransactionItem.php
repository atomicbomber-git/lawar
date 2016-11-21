<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    public $timestamps = false;

    /* Fields that can be mass filled */
    public $fillable = [
        "name", "description", "item_id", "transaction_id", "size", "price", "type", "stock_store", "stock_warehouse"
    ];
}