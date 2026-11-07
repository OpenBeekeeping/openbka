<?php

namespace Tests\Feature;

use App\Enums\Role;

class DashboardTest extends OpenBkaTestCase
{
    public function test_dashboard_shows_renewals_for_this_association_only(): void
    {
        $mine = $this->association('Mine');
        $theirs = $this->association('Theirs');
        $due = $this->member($mine->branches[0], 'Duesoon');
        $due->update(['renews_on' => today()->addDays(5)]);
        $other = $this->member($theirs->branches[0], 'Elsewhere');
        $other->update(['renews_on' => today()->addDays(5)]);

        $this->actingAs($this->admin($mine))
            ->get("/app/{$mine->slug}")
            ->assertOk()
            ->assertSee('Renewals due')
            ->assertSee('Pat Duesoon')
            ->assertDontSee('Pat Elsewhere')
            ->assertSee('Active members by branch');
    }

    public function test_branch_admins_see_only_their_branch_and_no_branch_ranking(): void
    {
        $association = $this->association();
        [$taunton, $bath] = $association->branches;
        $this->member($taunton, 'Taunton')->update(['renews_on' => today()->addDays(3)]);
        $this->member($bath, 'Bath')->update(['renews_on' => today()->addDays(3)]);

        $this->actingAs($this->admin($association, Role::BranchAdmin, $taunton))
            ->get("/app/{$association->slug}")
            ->assertOk()
            ->assertSee('Pat Taunton')
            ->assertDontSee('Pat Bath')
            ->assertDontSee('Active members by branch');
    }
}
