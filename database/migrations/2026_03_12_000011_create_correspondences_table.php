<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('correspondences', function (Blueprint $table) {
            $table->id();
            $table->string('ref_number')->unique(); // RFI-2026-001
            $table->string('subject');
            $table->enum('type', ['rfi','si','ncr','letter','submittal','mom','drawing','variation','payment_cert','early_warning','other']);
            $table->enum('direction', ['incoming','outgoing']);
            $table->string('from_party');
            $table->string('to_party');
            $table->date('date_sent_received');
            $table->date('response_due_date')->nullable();
            $table->date('response_date')->nullable();
            $table->enum('priority', ['low','medium','high','critical'])->default('medium');
            $table->enum('status', ['open','pending_response','responded','closed','overdue'])->default('open');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('correspondence_followups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('correspondence_id');
            $table->text('action_taken');
            $table->date('follow_up_date');
            $table->enum('status', ['pending','done','escalated'])->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('correspondence_followups');
        Schema::dropIfExists('correspondences');
    }
};