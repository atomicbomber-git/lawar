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

    public function save(array $options = []) {
        parent::save();


        /* --- Save cash update to cash.json --- */
        $file_path = "$GLOBALS[WEB_ROOT]/storage/cash.json";

        /* Load cash from cash.json */
        $cash_file = file_get_contents($file_path);
        $cash_data = json_decode($cash_file);

        /* Update and store */
        $cash_data->cash += $this->amount;
        $updated_cash_file = json_encode($cash_data);
        file_put_contents($file_path, $updated_cash_file);
    }
}