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

    /* Fields that can be mass filled */
    public $fillable = [
        "name", "description", "size", "type_id", "price", "entry_date", "stock_store", "stock_warehouse", "stock_event"
    ];
}