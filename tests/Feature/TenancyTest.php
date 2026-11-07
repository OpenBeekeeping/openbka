<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Resources\Members\Pages\ListMembers;
use App\Models\Member;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

class TenancyTest extends OpenBkaTestCase
{
    public function test_admins_cannot_open_another_associations_panel(): void
    {
        $mine = $this->association('Mine');
        $theirs = $this->association('Theirs');

        $this->actingAs($this->admin($mine))
            ->get("/app/{$theirs->slug}/members")
            ->assertNotFound();
    }

    public function test_member_list_only_shows_the_current_association(): void
    {
        $mine = $this->association('Mine');
        $theirs = $this->association('Theirs');
        $ours = $this->member($mine->branches[0], 'Ours');
        $other = $this->member($theirs->branches[0], 'Other');

        $this->actingAs($this->admin($mine));
        Filament::setCurrentPanel('app');
        Filament::setTenant($mine);
        Filament::bootCurrentPanel();

        Livewire::test(ListMembers::class)
            ->assertCanSeeTableRecords([$ours])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_branch_admins_only_see_their_branch(): void
    {
        $association = $this->association();
        [$taunton, $bath] = $association->branches;
        $inTaunton = $this->member($taunton, 'Taunton');
        $inBath = $this->member($bath, 'Bath');

        $this->actingAs($this->admin($association, Role::BranchAdmin, $taunton));
        Filament::setCurrentPanel('app');
        Filament::setTenant($association);
        Filament::bootCurrentPanel();

        Livewire::test(ListMembers::class)
            ->assertCanSeeTableRecords([$inTaunton])
            ->assertCanNotSeeTableRecords([$inBath]);
    }

    public function test_branch_admins_cannot_manage_admins(): void
    {
        $association = $this->association();

        $this->actingAs($this->admin($association, Role::BranchAdmin, $association->branches[0]))
            ->get("/app/{$association->slug}/admins")
            ->assertForbidden();
    }

    public function test_a_member_cannot_be_put_in_another_associations_branch(): void
    {
        $mine = $this->association('Mine');
        $theirs = $this->association('Theirs');

        $this->expectException(ValidationException::class);

        $member = new Member(['first_name' => 'A', 'last_name' => 'B', 'branch_id' => $theirs->branches[0]->id]);
        $member->association_id = $mine->id;
        $member->save();
    }

    public function test_member_pages_only_load_the_current_associations_records(): void
    {
        $mine = $this->association('Mine');
        $theirs = $this->association('Theirs');
        $other = $this->member($theirs->branches[0], 'Other');

        $this->actingAs($this->admin($mine))
            ->get("/app/{$mine->slug}/members/{$other->id}/edit")
            ->assertNotFound();
    }

    public function test_registration_is_off_when_self_hosted(): void
    {
        $this->assertFalse(Route::has('filament.app.auth.register'));
    }
}
