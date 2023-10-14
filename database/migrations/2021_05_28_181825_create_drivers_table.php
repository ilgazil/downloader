<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->string('name')->unique();
            $table->string('auth');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
}
