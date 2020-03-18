<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trip_genre');
            $table->string('trip_type');
            $table->string('group_name');
            $table->string('group_type');
            $table->string('group_desc');
            $table->string('country')->nullable();
            $table->json('nationality')->nullable();
            $table->json('domestic_trans')->nullable();
            $table->json('transportations')->nullable();
            $table->json('guides');
            $table->json('leaders')->nullable();
            $table->json('Destinations');
            $table->json('accomodations')->nullable();
            $table->date('arrival_date');
            $table->string('arrival_port')->nullable();
            $table->time('arrival_time')->nullable();
            $table->date('departure_date');
            $table->string('departure_port')->nullable();
            $table->time('departure_time')->nullable();
            $table->unsignedInteger('capacity')->default(49);
            $table->unsignedInteger('remain_chairs')->default(0);
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('users')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('trips');
    }
}
