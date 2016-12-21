<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Clerk extends Model
{

    public function transactions()
    {
        return $this->hasMany("App\Model\Transaction");
    }

    public $timestamps = false;
}