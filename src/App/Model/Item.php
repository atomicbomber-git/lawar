<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $timestamps = false;

    /* Reverse one to many relationship with App\Model\Type */
    public function type()
    {
        return $this->belongsTo("App\Model\Type");
    }

    // public function getPriceAttribute($value) {
    //     return number_format($value, 2, ".", ",");
    // }

    /* Fields that can be mass filled */
    public $fillable = [
        "name", "description", "size", "type_id", "price", "stock_store", "stock_warehouse"
    ];
}