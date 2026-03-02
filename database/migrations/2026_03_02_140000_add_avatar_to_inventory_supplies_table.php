<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventory_supplies', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('product_name');
        });
    }

    public function down()
    {
        Schema::table('inventory_supplies', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
};
