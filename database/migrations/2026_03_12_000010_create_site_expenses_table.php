<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('site_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('ref_number')->unique(); // EXP-2026-001
            $table->string('title');
            $table->enum('category', ['airtime','data','fuel','petty_cash','accommodation','per_diem','materials','transport','meals','other']);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('UGX');
            $table->date('expense_date');
            $table->unsignedBigInteger('employee_id')->nullable(); // who incurred it
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('status', ['draft','pending','approved','rejected'])->default('pending');
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('site_expenses'); }
};