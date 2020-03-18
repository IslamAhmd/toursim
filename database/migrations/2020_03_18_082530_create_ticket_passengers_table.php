<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketPassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_passengers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('natonality');
            $table->string('mobile');
            $table->unsignedBigInteger('base_fare');
            $table->unsignedBigInteger('tax');
            $table->unsignedBigInteger('total')->nullable();
            $table->unsignedBigInteger('commission');
            $table->unsignedBigInteger('net')->nullable();
            $table->unsignedBigInteger('profite');
            $table->unsignedBigInteger('rate')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('invoice_no');
            $table->text('notes');
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_passengers');
    }
}
