<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEarningsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('earnings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('market_id')->unsigned();
            $table->integer('total_orders')->unsigned()->default(0);
            $table->double('total_earning', 9, 2)->default(0);
            $table->double('admin_earning', 9, 2)->default(0);
            $table->double('market_earning', 9, 2)->default(0);
            $table->double('delivery_fee', 9, 2)->default(0);
            $table->double('tax', 9, 2)->default(0);
            $table->timestamps();
            $table->foreign('market_id')->references('id')->on('markets')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('earnings');
    }
}
