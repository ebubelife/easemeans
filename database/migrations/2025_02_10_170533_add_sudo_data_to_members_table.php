<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('easemeans_members', function (Blueprint $table) {
            //
          
            $table->string("sudo_customer_id")->nullable();
            $table->text("sudo_customer_data")->nullable();
            $table->string("sudo_account_id")->nullable();
            $table->text("sudo_account_data")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            //
        });
    }
};
