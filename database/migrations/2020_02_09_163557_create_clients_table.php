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
            $table->string('trip_type');
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->string('trip_name')->nullable();
            $table->date('date');
            $table->string('name');
            $table->unsignedInteger('contact_num');
            $table->string('category');
            $table->string('travel_agency');
            $table->unsignedInteger('single')->default(0);
            $table->unsignedInteger('double')->default(0);
            $table->unsignedInteger('triple')->default(0);
            $table->unsignedInteger('quad')->default(0);
            $table->unsignedInteger('total_rooms')->default(0);
            $table->unsignedInteger('adult')->default(0);
            $table->unsignedInteger('child')->default(0);
            $table->unsignedInteger('infant')->default(0);
            $table->unsignedInteger('total_people')->default(0);
            $table->unsignedInteger('seats_no')->default(0);
            $table->unsignedInteger('extra_seats')->default(0);
            $table->unsignedInteger('total_seats')->default(0);
            $table->json('seats_numbers');
            $table->string('booking');
            $table->string('seats');
            $table->string('status');
            $table->unsignedInteger('invoice_num');
            $table->text('notes');
            $table->timestamps();

            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');


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
