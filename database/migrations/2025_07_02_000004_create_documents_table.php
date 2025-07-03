<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path');
            $table->foreignId('contractor1_id')->constrained('contractors')->onDelete('cascade');
            $table->foreignId('contractor2_id')->nullable()->constrained('contractors')->onDelete('cascade');
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
