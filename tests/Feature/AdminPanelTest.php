<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Models\Student;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_admins_cannot_create_students_with_a_duplicate_phone(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Student::factory()->create(['phone' => '71994952']);

        Livewire::actingAs($this->admin())
            ->test(CreateStudent::class)
            ->fillForm([
                'name' => 'طالب جديد',
                'status' => StudentStatus::Active->value,
                'phone_country' => 'LB',
                'phone' => '71 994 952',
            ])
            ->call('create')
            ->assertHasFormErrors(['phone']);

        $this->assertDatabaseCount('students', 1);
    }

    public function test_admins_can_create_students_with_a_foreign_phone_number(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($this->admin())
            ->test(CreateStudent::class)
            ->fillForm([
                'name' => 'Jane',
                'status' => StudentStatus::Active->value,
                'phone_country' => 'FR',
                'phone' => '06 12 34 56 78',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('students', [
            'name' => 'Jane',
            'phone' => '+33612345678',
            'phone_country' => 'FR',
        ]);
    }
}
