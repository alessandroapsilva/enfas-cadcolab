<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('communication_email_logs')) return;

        Schema::create('communication_email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 30)->default('smtp')->index();
            $table->string('recipient', 255)->nullable()->index();
            $table->string('subject', 500)->nullable();
            $table->string('template_key', 120)->nullable()->index();
            $table->string('status', 30)->default('queued')->index();
            $table->string('provider', 80)->nullable();
            $table->string('message_id', 255)->nullable()->index();
            $table->text('error_message')->nullable();
            $table->string('triggered_by', 190)->nullable();
            $table->string('related_type', 80)->nullable();
            $table->unsignedBigInteger('related_id')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_email_logs');
    }
};
