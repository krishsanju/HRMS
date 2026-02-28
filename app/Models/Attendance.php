<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = ['employee_id','check_in','check_out'];

    /**
     * Get the working hours for the attendance record.
     *
     * @return float|null
     */
    public function getWorkingHoursAttribute()
    {
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);
            return round($checkIn->diffInMinutes($checkOut) / 60, 2); // Hours with 2 decimal places
        }
        return null;
    }
}