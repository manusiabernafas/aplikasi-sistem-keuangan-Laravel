<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('debit', 20, 2)->default(0);
            $table->decimal('credit', 20, 2)->default(0);
            $table->date('entry_date');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['transaction_id']);
            $table->index(['account_id', 'entry_date']);
            $table->index(['category_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_entries');
    }
};
