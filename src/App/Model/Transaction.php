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
        "clerk_id", "datetime", "is_finished"
    ];
}