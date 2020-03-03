<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisaAcceptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visa_accepts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('visa_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->date('issue_date');
            $table->date('validity_from');
            $table->date('validity_to');
            $table->unsignedInteger('number');
            $table->unsignedBigInteger('payment');
            $table->string('status');
            $table->unsignedInteger('invoice_no');
            $table->timestamps();

            $table->foreign('visa_id')->references('id')->on('visas')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visa_accepts');
    }
}
