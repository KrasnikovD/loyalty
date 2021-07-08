<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Sales;

class ModifySalesTable5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->integer('bill_id')->nullable()->default(null)->change();
            $table->integer('card_id')->nullable()->default(null)->change();
            $table->integer('amount_now');
            $table->integer('status')->default(Sales::STATUS_PRE_ORDER);
            $table->dropColumn('outlet_name');
        });

        DB::statement("ALTER TABLE sales AUTO_INCREMENT = 10000");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
