<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    public $timestamps = false;

    /* Fields that can be mass filled */
    public $fillable = [
        "name", "description", "size", "type", "quantity"
    ];
}