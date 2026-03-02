<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventory_supplies', function (Blueprint $table) {
            $table->string('category_color', 7)->nullable()->after('category');
        });
    }

    public function down()
    {
        Schema::table('inventory_supplies', function (Blueprint $table) {
            $table->dropColumn('category_color');
        });
    }
};
