<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('issue_date');
            $table->string('issuing_airline');
            $table->string('issuing_agent');
            $table->string('business_type');
            $table->string('booking_reference');
            $table->string('booking_status');
            $table->unsignedInteger('ticket_number');
            $table->string('ticket_type');
            $table->string('flight_type');
            $table->string('flight_direction');
            $table->unsignedBigInteger('user_id')->nullable();
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
        Schema::dropIfExists('tickets');
    }
}
