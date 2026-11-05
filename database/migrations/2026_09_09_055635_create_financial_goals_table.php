<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->string('goal_name');
            $table->decimal('target_amount', 20, 2);
            $table->decimal('current_amount', 20, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['IN_PROGRESS', 'ACHIEVED', 'CANCELLED'])->default('IN_PROGRESS');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_goals');
    }
};
