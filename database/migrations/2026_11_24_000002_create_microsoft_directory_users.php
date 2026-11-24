<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('microsoft_directory_users', function (Blueprint $table) {
            $table->uuid('tenant_id');
            $table->uuid('object_id');
            $table->primary(['tenant_id','object_id']);
            $table->string('upn');
            $table->string('email')->nullable();
            $table->string('display_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('enabled')->nullable();
            $table->string('user_type')->nullable();
            $table->json('licenses');
            $table->timestamp('last_seen_at');
            $table->unsignedBigInteger('colaborador_id')->nullable();
        });
    }
    public function down(): void {
        Schema::dropIfExists('microsoft_directory_users');
    }
};
