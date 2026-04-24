<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Model implements Authenticatable
{
    use AuthenticatableTrait, HasApiTokens;

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'department_id',
        'joining_date',
        'status',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}