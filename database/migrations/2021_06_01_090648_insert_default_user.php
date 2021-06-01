<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Users;

class InsertDefaultUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $user = new Users;
        $user->first_name = 'admin';
        $user->second_name = 'admin';
        $user->password = '56c88ccbd5ed243738b643b5ca8446a9';
        $user->phone = '+111111111111';
        $user->type = 0;
        $user->token = sha1(microtime() . 'salt' . time());
        $user->save();
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
