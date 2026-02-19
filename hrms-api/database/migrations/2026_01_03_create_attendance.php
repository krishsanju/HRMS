<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration{
public function up(){
Schema::create('attendances',function(Blueprint $t){
$t->id();
$t->unsignedBigInteger('employee_id');
$t->timestamp('check_in')->nullable();
$t->timestamp('check_out')->nullable();
$t->timestamps();
});
}
public function down(){Schema::dropIfExists('attendances');}
};
