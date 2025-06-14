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
    public function up(): void
    {
        Schema::table('moniteurs', function (Blueprint $table) {
            $table->string('num_telephone')->nullable();
            $table->string('num_telephone_2')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('commune')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('moniteurs', function (Blueprint $table) {
            $table->dropColumn(['num_telephone', 'num_telephone_2', 'email', 'commune']);
        });
    }
};
