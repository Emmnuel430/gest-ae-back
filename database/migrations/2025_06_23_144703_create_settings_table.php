<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });

        DB::table('settings')->insert([
            ['key' => 'scolarite_recyclage', 'value' => '60000'],
            ['key' => 'scolarite_reduction', 'value' => '25000'],
            ['key' => 'scolarite_A', 'value' => '30000'],
            ['key' => 'scolarite_B', 'value' => '50000'],
            ['key' => 'scolarite_AB', 'value' => '100000'],
            ['key' => 'scolarite_BCDE', 'value' => '120000'],
            ['key' => 'scolarite_ABCDE', 'value' => '150000'],
            ['key' => 'scolarite_par_defaut', 'value' => '25000'],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
