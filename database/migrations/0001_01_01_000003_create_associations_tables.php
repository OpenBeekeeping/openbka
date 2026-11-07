<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The tenant. An association either has branches (each with members) or holds members directly.
        Schema::create('associations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('charity_number')->nullable();

            // Laravel Cashier (hosted mode only). The association is the paying customer.
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['association_id', 'slug']);
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            // Set when a member also has a sign-in account.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('membership_number')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('town')->nullable();
            $table->string('postcode')->nullable();
            $table->string('status')->default('active');
            $table->date('joined_on')->nullable();
            $table->date('renews_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['association_id', 'membership_number']);
            $table->index(['association_id', 'branch_id']);
        });

        // Who can manage an association. A branch_id limits a user to one branch.
        Schema::create('association_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('admin');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['association_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('association_user');
        Schema::dropIfExists('members');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('associations');
    }
};
