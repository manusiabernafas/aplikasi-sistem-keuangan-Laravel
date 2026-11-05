<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('transaction_type', ['INCOME', 'EXPENSE', 'TRANSFER']);
            $table->decimal('amount', 20, 2);
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->foreignId('destination_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->enum('frequency', ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY']);
            $table->unsignedSmallInteger('interval_count')->default(1);
            $table->unsignedTinyInteger('day_of_month')->nullable();
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_run_date');
            $table->date('last_run_date')->nullable();
            $table->enum('status', ['ACTIVE', 'PAUSED', 'COMPLETED'])->default('ACTIVE');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'next_run_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
