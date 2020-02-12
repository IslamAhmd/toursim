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
            $table->string('trip_type');
            $table->string('group_name');
            $table->string('group_type');
            $table->string('group_desc');
            $table->json('transportations');
            $table->json('guides');
            $table->json('accomodations')->nullable();
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('remain_chairs')->default(0);
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('company_name')->nullable();
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
