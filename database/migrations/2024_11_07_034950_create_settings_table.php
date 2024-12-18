<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->integer('value');
            $table->timestamps();
        });

        DB::table('settings')->insert([
            ['key' => 'predictedSalesDay', 'value' => 1],
            ['key' => 'historicalDataDays', 'value' => 90],
            ['key' => 'predictedSalesMonth', 'value' => 1],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
