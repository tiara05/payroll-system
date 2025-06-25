<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryRequest extends Model {
    protected $fillable = [
        'user_id', 'employee_id', 'base_salary', 'bonus', 'pph', 'total_salary', 'status', 'approved_by', 'approved_at', 'payment_proof'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}