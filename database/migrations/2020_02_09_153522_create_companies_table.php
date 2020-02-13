<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('address');
            $table->string('telephone');
            $table->string('mobile');
            $table->string('fax');
            $table->string('oficial_mail');
            $table->string('finance_mail');
            $table->string('operation_mail');
            $table->string('tax_card');
            $table->string('commercial_register');
            $table->unsignedBigInteger('licence_num');
            $table->string('manager');
            $table->string('manager_phone');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('status')->default(1);
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
