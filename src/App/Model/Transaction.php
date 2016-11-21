<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public $timestamps = false;

    public function clerk() {
        return $this->belongsTo("App\Model\Clerk");
    }

    /* Fields that can be mass filled */
    public $fillable = [
        "customer_name", "customer_phone", "clerk_id", "datetime", "is_finished"
    ];
}