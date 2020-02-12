<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->string('name');
            $table->unsignedInteger('contact_num');
            $table->string('category');
            $table->string('travel_agency');
            $table->string('room_category')->nullable();
            $table->string('meal_plan')->nullable();
            $table->unsignedInteger('single')->nullable();
            $table->unsignedInteger('double')->nullable();
            $table->unsignedInteger('triple')->nullable();
            $table->unsignedInteger('quad')->nullable();
            $table->unsignedInteger('total_rooms')->nullable();
            $table->unsignedInteger('adult')->nullable();
            $table->unsignedInteger('child')->nullable();
            $table->unsignedInteger('infant')->nullable();
            $table->unsignedInteger('total_people')->nullable();
            $table->unsignedInteger('seats_num')->nullable();
            $table->unsignedInteger('extra_seats')->nullable();
            $table->unsignedInteger('total_seats')->nullable();
            $table->string('booking');
            $table->string('seats');
            $table->string('status');
            $table->unsignedInteger('invoice_num');
            $table->text('notes');
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('dest_id')->nullable();
            $table->string('dest_name')->nullable();
            $table->timestamps();

            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('dest_id')->references('id')->on('destinations')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
