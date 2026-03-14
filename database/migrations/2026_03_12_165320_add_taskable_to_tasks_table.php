<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'taskable_type')) {
                $table->nullableMorphs('taskable');
            }
            if (!Schema::hasColumn('tasks', 'priority')) {
                $table->string('priority')->default('medium');
            }
            if (!Schema::hasColumn('tasks', 'board_position')) {
                $table->integer('board_position')->default(0);
            }
            if (!Schema::hasColumn('tasks', 'board_status')) {
                $table->string('board_status')->default('todo');
            }
            if (!Schema::hasColumn('tasks', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained();
            }
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $columns = ['taskable_type', 'taskable_id', 'priority', 'board_position', 'board_status', 'user_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};