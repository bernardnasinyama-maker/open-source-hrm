<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('nssf_number')->nullable()->after('national_id');
            $table->decimal('basic_salary', 12, 2)->nullable()->after('nssf_number')
                  ->comment('Monthly basic salary in UGX');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['nssf_number', 'basic_salary']);
        });
    }
};
