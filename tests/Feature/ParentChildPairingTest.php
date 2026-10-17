<?php

namespace Tests\Feature;

use App\Models\Parents;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentChildPairingTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_dashboard_renders_with_empty_children_by_default(): void
    {
        $parentUser = User::factory()->create([
            'role' => 'parent',
        ]);

        $response = $this->actingAs($parentUser)->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('ParentDashboard')
            ->has('children', 0)
        );
    }

    public function test_child_can_regenerate_pairing_code(): void
    {
        $childUser = User::factory()->create([
            'role' => 'child',
        ]);
        $student = Student::create([
            'user_id' => $childUser->id,
            'name' => $childUser->name,
            'pairing_code' => 'LNT-OLD123',
        ]);

        $response = $this->actingAs($childUser)->post('/profile/pairing-code/regenerate');

        $response->assertRedirect();
        $student->refresh();
        $this->assertNotEquals('LNT-OLD123', $student->pairing_code);
        $this->assertStringStartsWith('LNT-', $student->pairing_code);
    }

    public function test_parent_can_pair_child_with_valid_code(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $childUser = User::factory()->create(['role' => 'child']);
        $student = Student::create([
            'user_id' => $childUser->id,
            'name' => 'Budi Kecil',
            'pairing_code' => 'LNT-ABC999',
        ]);

        $response = $this->actingAs($parentUser)->post('/parent/children/pair', [
            'pairing_code' => 'lnt-abc999', // Test case-insensitivity
        ]);

        $response->assertRedirect();
        $student->refresh();
        $this->assertNotNull($student->parent_id);
        $this->assertEquals($parentUser->id, $student->parent->user_id);

        // Verify child appears in dashboard
        $dashboardResponse = $this->actingAs($parentUser)->get('/dashboard');
        $dashboardResponse->assertInertia(fn ($page) => $page
            ->component('ParentDashboard')
            ->has('children', 1)
            ->where('children.0.name', 'Budi Kecil')
        );
    }

    public function test_parent_cannot_pair_with_invalid_code(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);

        $response = $this->actingAs($parentUser)->post('/parent/children/pair', [
            'pairing_code' => 'INVALID-CODE',
        ]);

        $response->assertSessionHasErrors(['pairing_code']);
    }

    public function test_parent_can_unpair_child(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = Parents::create([
            'user_id' => $parentUser->id,
            'name' => $parentUser->name,
            'email' => $parentUser->email,
        ]);
        $childUser = User::factory()->create(['role' => 'child']);
        $student = Student::create([
            'user_id' => $childUser->id,
            'parent_id' => $parent->id,
            'name' => 'Budi Kecil',
            'pairing_code' => 'LNT-ABC999',
        ]);

        $response = $this->actingAs($parentUser)->delete("/parent/children/{$student->id}/unpair");

        $response->assertRedirect();
        $student->refresh();
        $this->assertNull($student->parent_id);
    }
}
