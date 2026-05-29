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
        Schema::table('email_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('email_logs', 'website')) {
                $table->string('website')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('email_logs', 'hr_name')) {
                $table->string('hr_name')->nullable()->after('website');
            }
            if (!Schema::hasColumn('email_logs', 'position')) {
                $table->string('position')->nullable()->after('hr_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropColumn(['website', 'hr_name', 'position']);
        });
    }
};
