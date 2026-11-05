<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 30)->unique();
            $table->date('transaction_date');
            $table->enum('transaction_type', ['INCOME', 'EXPENSE', 'TRANSFER', 'ADJUSTMENT']);
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->foreignId('destination_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('amount', 20, 2);
            $table->string('description')->nullable();
            $table->string('reference_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['POSTED', 'VOID'])->default('POSTED');
            $table->boolean('is_reconciled')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['transaction_date', 'transaction_type']);
            $table->index(['account_id', 'transaction_date']);
            $table->index(['category_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
