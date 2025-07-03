<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilePathToTemplatesTable extends Migration
{
    public function up()
    {
        Schema::table('templates', function (Blueprint $table) {
            if (!Schema::hasColumn('templates', 'file_path')) {
                $table->string('file_path', 255)->nullable()->after('content');
            }
        });
    }

    public function down()
    {
        Schema::table('templates', function (Blueprint $table) {
            if (Schema::hasColumn('templates', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });
    }
}
