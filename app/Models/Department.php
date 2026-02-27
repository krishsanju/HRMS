<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the employees for the Department.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
