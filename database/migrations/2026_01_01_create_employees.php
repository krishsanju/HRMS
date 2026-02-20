<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration{
public function up(){
Schema::create('employees',function(Blueprint $t){
$t->id();
$t->string('employee_code')->unique();
$t->string('first_name');
$t->string('last_name')->nullable();
$t->string('email')->unique();
$t->unsignedBigInteger('department_id')->nullable();
$t->date('joining_date')->nullable();
$t->enum('status',['active','inactive'])->default('active');
$t->timestamps();
});
}
public function down(){Schema::dropIfExists('employees');}
};
