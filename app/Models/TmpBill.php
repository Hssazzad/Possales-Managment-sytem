<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TmpBill extends Model
{
    protected $table = 'tbl_bill_tmp';

    public $timestamps = false;

    protected $fillable = [
        'main_code',
        'sub_code',
        'name',
        'amount',
        'patient_code',
        'bill_no',
        'status',
    ];

    public function patient()
    {
        return $this->belongsTo(Customer::class, 'patient_code', 'code');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_code', 'code');
    }
}
