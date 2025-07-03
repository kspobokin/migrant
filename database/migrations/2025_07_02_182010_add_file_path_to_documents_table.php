<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilePathToDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'file_path')) {
                $table->string('file_path', 255)->nullable()->after('content');
            }
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });
    }
}
