<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_rules', function (Blueprint $table) {
            $table->id();
            $table->date('start_dt')->nullable()->default(null);
            $table->unsignedInteger('month')->nullable()->default(null);
            $table->unsignedInteger('day')->nullable()->default(null);
            $table->unsignedInteger('duration')->nullable()->default(null);
            $table->unsignedBigInteger('field_id');
            $table->foreign('field_id')->references('id')->on('fields');
            $table->tinyInteger('enabled')->default(1);
            $table->unsignedInteger('value')->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('bonus_rules');
    }
}
