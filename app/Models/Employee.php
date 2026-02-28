<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Employee extends Model implements Authenticatable
{
    use AuthenticatableTrait, Notifiable, HasApiTokens;

    protected $fillable = ['employee_code','first_name','last_name','email','department_id','joining_date','status', 'password'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Set the employee's password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
}