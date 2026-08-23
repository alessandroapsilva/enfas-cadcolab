<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->longText('html_signature');
            $table->string('token', 100);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_signatures');
    }
};
