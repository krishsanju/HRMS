<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration{
public function up(){
Schema::create('leave_requests',function(Blueprint $t){
$t->id();
$t->unsignedBigInteger('employee_id');
$t->date('from_date');
$t->date('to_date');
$t->string('status')->default('pending');
$t->timestamps();
});
}
public function down(){Schema::dropIfExists('leave_requests');}
};
