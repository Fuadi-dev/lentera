<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained('users')->cascadeOnDelete();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('pairing_code', 12)->nullable()->unique()->after('name');
        });

        // Generate pairing code for existing students
        $existingStudents = DB::table('students')->whereNull('pairing_code')->get();
        foreach ($existingStudents as $student) {
            $code = 'LNT-'.strtoupper(Str::random(6));
            DB::table('students')
                ->where('id', $student->id)
                ->update(['pairing_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('pairing_code');
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
