<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added
use Illuminate\Database\Eloquent\Relations\HasMany; // Added

class Department extends Model
{
    use HasFactory; // Added
    protected $fillable = ['name'];

    /**
     * Get the employees for the department.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}