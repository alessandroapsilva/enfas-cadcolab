<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('communication_logs')) {
            Schema::create('communication_logs', function (Blueprint $table) {
                $table->id();
                $table->string('channel', 32)->index();
                $table->string('direction', 16)->default('outbound')->index();
                $table->string('recipient')->nullable()->index();
                $table->string('subject')->nullable();
                $table->string('template')->nullable();
                $table->string('status', 32)->default('queued')->index();
                $table->string('provider')->nullable();
                $table->string('provider_message_id')->nullable()->index();
                $table->unsignedBigInteger('pre_registro_id')->nullable()->index();
                $table->string('actor')->nullable();
                $table->text('error_message')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
