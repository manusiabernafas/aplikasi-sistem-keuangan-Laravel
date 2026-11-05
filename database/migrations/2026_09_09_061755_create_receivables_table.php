<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->string('receivable_number', 30)->unique();
            $table->string('debtor_name');
            $table->decimal('amount', 20, 2);
            $table->date('receivable_date');
            $table->date('due_date');
            $table->enum('status', ['OUTSTANDING', 'PARTIALLY_PAID', 'PAID', 'OVERDUE'])->default('OUTSTANDING');
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivables');
    }
};
