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
        Schema::table('companies', function (Blueprint $table) {
            // Drop the nif column if it exists
            if (Schema::hasColumn('companies', 'nif')) {
                $table->dropColumn('nif');
            }
            // Add tax_id column if it doesn't exist
            if (!Schema::hasColumn('companies', 'tax_id')) {
                $table->string('tax_id')->nullable()->after('city');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Add nif back
            if (!Schema::hasColumn('companies', 'nif')) {
                $table->string('nif')->unique()->encrypted()->after('city');
            }
            // Drop tax_id
            if (Schema::hasColumn('companies', 'tax_id')) {
                $table->dropColumn('tax_id');
            }
        });
    }
};
