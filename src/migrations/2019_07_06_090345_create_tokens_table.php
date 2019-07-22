<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('consumer_id')->unsigned();
            $table->string('user_type');
            $table->bigInteger('user_id');
            $table->text('token');
            $table->tinyInteger('revoked')->default(0);
            $table->timestamp('expires_in')->nullable();
            $table->timestamps();

            $table->foreign('consumer_id')->references('id')->on('consumers')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tokens');
    }
}
