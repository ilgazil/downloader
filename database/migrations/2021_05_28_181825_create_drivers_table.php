<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->string('name')->unique();
            $table->string('login');
            $table->string('password');
            $table->string('cookie');
        });
    }

    public function down()
    {
        Schema::dropIfExists('drivers');
    }
}
