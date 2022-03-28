<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quickbooks_tokens', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignIdFor(config('quickbooks.user.model'), config('quickbooks.user.keys.foreign'))
                ->constrained()
                ->onDelete('cascade');

            $table->unsignedBigInteger('realm_id');
            $table->longText('access_token');
            $table->dateTime('access_token_expires_at');
            $table->string('refresh_token');
            $table->datetime('refresh_token_expires_at');
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
        Schema::dropIfExists('quickbooks_tokens');
    }
};
