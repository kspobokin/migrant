<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractorsTable extends Migration
{
    public function up()
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('last_name_ru');
            $table->string('first_name_ru');
            $table->string('patronymic_ru');
            $table->string('last_name_lat')->nullable();
            $table->string('first_name_lat')->nullable();
            $table->string('patronymic_lat')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('inn')->nullable();
            $table->string('insurance_policy')->nullable();
            $table->string('registration_address')->nullable();
            $table->string('type');
            $table->string('role');
            $table->json('extra_fields')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contractors');
    }
}
