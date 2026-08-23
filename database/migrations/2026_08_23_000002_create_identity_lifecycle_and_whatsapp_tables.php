<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('identity_access_policies')) {
            Schema::create('identity_access_policies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('department')->nullable()->index();
                $table->string('title')->nullable()->index();
                $table->string('ldap_group')->nullable()->index();
                $table->string('profile')->default('Operador');
                $table->json('m365_groups')->nullable();
                $table->json('google_groups')->nullable();
                $table->json('applications')->nullable();
                $table->boolean('enabled')->default(true)->index();
                $table->unsignedInteger('priority')->default(100);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('identity_provisioning_events')) {
            Schema::create('identity_provisioning_events', function (Blueprint $table) {
                $table->id();
                $table->string('correlation_id', 64)->unique();
                $table->string('username')->nullable()->index();
                $table->string('event_type', 40)->index();
                $table->string('target', 40)->nullable()->index();
                $table->string('status', 30)->default('pending')->index();
                $table->json('payload')->nullable();
                $table->text('message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('whatsapp_conversations')) {
            Schema::create('whatsapp_conversations', function (Blueprint $table) {
                $table->id();
                $table->string('wa_id', 64)->unique();
                $table->string('phone', 32)->index();
                $table->string('contact_name')->nullable();
                $table->string('status', 30)->default('open')->index();
                $table->string('assigned_to')->nullable()->index();
                $table->timestamp('last_message_at')->nullable()->index();
                $table->unsignedInteger('unread_count')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->nullable()->constrained('whatsapp_conversations')->nullOnDelete();
                $table->string('meta_message_id')->nullable()->unique();
                $table->string('direction', 10)->index();
                $table->string('type', 30)->default('text');
                $table->text('body')->nullable();
                $table->json('payload')->nullable();
                $table->string('status', 30)->nullable()->index();
                $table->timestamp('message_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('whatsapp_webhook_events')) {
            Schema::create('whatsapp_webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_key', 100)->nullable()->index();
                $table->json('payload');
                $table->boolean('processed')->default(false)->index();
                $table->text('error')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_webhook_events');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_conversations');
        Schema::dropIfExists('identity_provisioning_events');
        Schema::dropIfExists('identity_access_policies');
    }
};
