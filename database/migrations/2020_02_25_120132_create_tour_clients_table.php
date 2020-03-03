<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tour_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client');
            $table->string('passport_no');
            $table->unsignedBigInteger('tour_id')->nullable();
            $table->string('accomodation');
            $table->unsignedInteger('adult');
            $table->unsignedInteger('child')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->unsignedBigInteger('payment')->nullable();
            $table->text('notes');
            $table->timestamps();


            $table->foreign('tour_id')->references('id')->on('trip_tours')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tour_clients');
    }
}
