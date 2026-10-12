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
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->foreignUuid('parent_id')->nullable()->change();
            $table->foreign('parent_id')->references('id')->on('parents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
            $table->dropForeign(['parent_id']);
            $table->foreignUuid('parent_id')->nullable(false)->change();
            $table->foreign('parent_id')->references('id')->on('parents')->cascadeOnDelete();
        });
    }
};
