<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Services\File\Download;

class CreateDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->string('url')->unique();
            $table->string('hostName');
            $table->string('fileName');
            $table->string('fileSize');
            $table->string('target');
            $table->tinyText('progress');
            $table->enum(
                'state',
                [Download::$PENDING, Download::$RUNNING, Download::$PAUSED, Download::$DONE]
            );
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
        Schema::dropIfExists('downloads');
    }
}
