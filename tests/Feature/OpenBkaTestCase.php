<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Association;
use App\Models\Branch;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class OpenBkaTestCase extends TestCase
{
    use RefreshDatabase;

    protected function association(string $name = 'Somerset BKA'): Association
    {
        $association = Association::create(['name' => $name]);
        $association->branches()->create(['name' => 'Taunton']);
        $association->branches()->create(['name' => 'Bath']);

        return $association;
    }

    protected function admin(Association $association, Role $role = Role::Owner, ?Branch $branch = null): User
    {
        $user = User::factory()->create();
        $association->users()->attach($user, ['role' => $role->value, 'branch_id' => $branch?->id]);

        return $user;
    }

    protected function member(Branch $branch, string $lastName): Member
    {
        $member = new Member(['first_name' => 'Pat', 'last_name' => $lastName, 'branch_id' => $branch->id]);
        $member->association_id = $branch->association_id;
        $member->save();

        return $member;
    }
}
