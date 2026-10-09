<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_hash', 64)->nullable();
            $table->string('tool_slug');
            $table->string('usage_type');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'tool_slug', 'usage_type', 'created_at'], 'usage_events_user_tool_idx');
            $table->index(['guest_hash', 'tool_slug', 'usage_type', 'created_at'], 'usage_events_guest_tool_idx');
            $table->index(['usage_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_events');
    }
};
