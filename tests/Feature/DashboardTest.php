<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_is_shown_the_parent_dashboard(): void
    {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $response = $this->actingAs($parent)->get('/dashboard');

        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ParentDashboard')
            ->has('children', 3)
            ->where('children.0.name', 'Alya Putri'));
    }

    public function test_child_keeps_the_learning_dashboard(): void
    {
        $child = User::factory()->create([
            'role' => 'child',
        ]);

        $response = $this->actingAs($child)->get('/dashboard');

        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
    }
}
