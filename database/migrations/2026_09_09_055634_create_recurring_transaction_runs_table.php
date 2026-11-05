<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_transaction_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_transaction_id')->constrained()->cascadeOnDelete();
            $table->date('run_date');
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['SUCCESS', 'FAILED'])->default('SUCCESS');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['recurring_transaction_id', 'run_date'], 'recurring_tx_runs_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_transaction_runs');
    }
};
