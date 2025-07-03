<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('file_path')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('templates');
    }
}
