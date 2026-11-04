<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('association_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('reference_month'); // Primeiro dia do mês de competência (ex: 2026-11-01)
            $table->date('due_date');        // Data limite de pagamento (5º dia: 2026-11-05)
            $table->decimal('amount', 10, 2)->default(50.00);
            $table->string('status')->default('paid'); // 'paid', 'pending', 'overdue', 'cancelled'
            $table->string('payment_method')->default('pix'); // 'pix', 'dinheiro', 'transferencia', 'outro'
            $table->string('payment_proof')->nullable(); // caminho para o comprovante
            $table->string('receipt_number')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Garante que cada usuário tenha apenas 1 registro por mês de competência
            $table->unique(['user_id', 'reference_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
