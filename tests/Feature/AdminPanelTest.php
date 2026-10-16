<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guests_are_redirected_to_the_panel_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_authenticated_non_admins_are_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admins_can_view_the_dashboard(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertOk();
    }

    public function test_admins_can_open_every_resource_list_and_create_page(): void
    {
        $admin = $this->admin();

        $resources = Filament::getPanel('admin')->getResources();

        $this->assertNotEmpty($resources, 'No Filament resources were discovered.');

        foreach ($resources as $resource) {
            $this->actingAs($admin)
                ->get($resource::getUrl('index'))
                ->assertOk();

            $this->actingAs($admin)
                ->get($resource::getUrl('create'))
                ->assertOk();
        }
    }
}
