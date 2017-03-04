<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeodataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geodata', function (Blueprint $table) {
            $table->increments('id');
            $table->float('longitude', 10, 4);
            $table->float('latitude', 10, 4);
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('zipcode')->nullable();
            $table->integer('useragent_id')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('geodata');
    }
}
