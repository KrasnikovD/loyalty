<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id');
            $table->foreign('card_id')->references('id')->on('cards');
            $table->unsignedBigInteger('author_id')->nullable()->default(null);
            $table->string('type');
            $table->text('data')->nullable()->default(null);
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
        Schema::dropIfExists('card_history');
    }
}
