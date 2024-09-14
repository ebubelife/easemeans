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
        Schema::create('easemeans_members', function (Blueprint $table) {
            $table->id();
           
            $table->string("first_name")->nullable();
            $table->string("last_name")->nullable();
            $table->string("email")->nullable();
            $table->string("phone")->nullable();
            $table->string("bvn")->nullable();
            $table->string("dob")->nullable();
            $table->text("password")->nullable();
            $table->text("selfie_path")->nullable();
            $table->string("transaction_pin")->nullable();
            $table->float("deposit_balance")->nullable();
            
            $table->boolean("user_active")->default(true);
            $table->string("last_app_open")->nullable();

            $table->string("email_verification_code")->nullable();
            $table->string("phone_verification_code")->nullable();

           

            
            $table->boolean("selfie_verified")->default(false);
            $table->boolean("bvn_verified")->default(false);
            $table->string("bvn_verification_attempts")->nullable();
            $table->boolean("email_verified")->default(false);
            $table->boolean("phone_verified")->default(false);


            
            $table->text("address")->nullable();

            $table->text("safehaven_number")->nullable();
 
            $table->text("image")->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
