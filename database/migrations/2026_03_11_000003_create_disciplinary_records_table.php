<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('raised_by')->constrained('employees')->onDelete('cascade');
            $table->date('incident_date');
            $table->enum('type', [
                'verbal_warning',
                'written_warning',
                'final_warning',
                'suspension',
                'demotion',
                'termination',
                'misconduct',
                'absenteeism',
                'insubordination',
                'other',
            ]);
            $table->enum('severity', ['minor', 'moderate', 'serious', 'gross']);
            $table->string('subject');
            $table->text('incident_description');
            $table->text('action_taken');
            $table->text('outcome')->nullable();
            $table->date('review_date')->nullable();
            $table->enum('status', ['open', 'under_review', 'resolved', 'appealed'])->default('open');
            $table->boolean('employee_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('supporting_document')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_records');
    }
};
