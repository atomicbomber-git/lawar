<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    public $timestamps = false;

    /* A 'Type' can has many 'Item's that belong to it */
    public function items()
    {
        return $this->hasMany("App\Model\Item", "type");
    }
}