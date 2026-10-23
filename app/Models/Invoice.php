<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'patient_code',
        'total',
        'paid',
        'due',
        'bill_no',
        'created_at',
    ];

    public function patient()
    {
        return $this->belongsTo(Customer::class, 'patient_id', 'id');
    }

    public function billItems()
    {
        return $this->hasMany(TmpBill::class, 'bill_no', 'bill_no')->where('status', 1);
    }
}
