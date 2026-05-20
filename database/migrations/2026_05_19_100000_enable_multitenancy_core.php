<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->foreignId('owner_user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->json('settings')->nullable()->after('tax_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('active_company_id')->nullable()->after('active')->constrained('companies')->nullOnDelete();
        });

        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->boolean('is_owner')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'user_id']);
        });

        foreach (['entities', 'contacts', 'articles', 'proposals', 'orders', 'supplier_orders', 'invoices', 'calendar_events', 'permission_groups'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
                $table->index('company_id');
            });
        }

        $defaultCompany = Company::query()->first();

        if (!$defaultCompany) {
            $defaultCompany = Company::create([
                'name' => 'Tenant Principal',
                'slug' => 'tenant-principal',
                'address' => '',
                'postal_code' => '',
                'city' => '',
                'tax_id' => null,
                'settings' => [
                    'onboarding' => [
                        'branding' => false,
                        'users' => false,
                        'permissions' => false,
                    ],
                    'preferences' => [
                        'default_landing_page' => '/dashboard',
                    ],
                ],
            ]);
        }

        if (empty($defaultCompany->slug)) {
            $defaultCompany->slug = 'tenant-' . $defaultCompany->id;
            $defaultCompany->save();
        }

        DB::table('users')->whereNull('active_company_id')->update(['active_company_id' => $defaultCompany->id]);

        $firstUserId = User::query()->orderBy('id', 'asc')->value('id');

        if ($firstUserId && !$defaultCompany->owner_user_id) {
            $defaultCompany->owner_user_id = $firstUserId;
            $defaultCompany->save();
        }

        User::query()->select('id')->chunkById(200, function ($users) use ($defaultCompany) {
            foreach ($users as $user) {
                DB::table('company_user')->updateOrInsert(
                    ['company_id' => $defaultCompany->id, 'user_id' => $user->id],
                    [
                        'role' => $user->id === $defaultCompany->owner_user_id ? 'owner' : 'member',
                        'is_owner' => $user->id === $defaultCompany->owner_user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });

        foreach (['entities', 'contacts', 'articles', 'proposals', 'orders', 'supplier_orders', 'invoices', 'calendar_events', 'permission_groups'] as $tableName) {
            DB::table($tableName)->whereNull('company_id')->update(['company_id' => $defaultCompany->id]);
        }
    }

    public function down(): void
    {
        foreach (['entities', 'contacts', 'articles', 'proposals', 'orders', 'supplier_orders', 'invoices', 'calendar_events', 'permission_groups'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropIndex(['company_id']);
                $table->dropColumn('company_id');
            });
        }

        Schema::dropIfExists('company_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_company_id']);
            $table->dropColumn('active_company_id');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['owner_user_id']);
            $table->dropColumn(['slug', 'owner_user_id', 'settings']);
        });
    }
};
