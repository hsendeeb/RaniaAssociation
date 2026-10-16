<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminEnrollmentApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_admins_can_approve_an_enrollment(): void
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::Pending,
            'enrolled_at' => null,
        ]);

        Livewire::test(ListEnrollments::class)
            ->callTableAction('approve', $enrollment)
            ->assertHasNoActionErrors();

        $enrollment->refresh();

        $this->assertSame(EnrollmentStatus::Active, $enrollment->status);
        $this->assertNotNull($enrollment->enrolled_at);
    }

    public function test_admins_can_cancel_an_enrollment(): void
    {
        $enrollment = Enrollment::factory()->create(['status' => EnrollmentStatus::Pending]);

        Livewire::test(ListEnrollments::class)
            ->callTableAction('cancel', $enrollment)
            ->assertHasNoActionErrors();

        $this->assertSame(EnrollmentStatus::Cancelled, $enrollment->fresh()->status);
    }

    public function test_admins_can_approve_enrollments_in_bulk(): void
    {
        $enrollments = Enrollment::factory()->count(2)->create([
            'status' => EnrollmentStatus::Pending,
            'enrolled_at' => null,
        ]);

        Livewire::test(ListEnrollments::class)
            ->callTableBulkAction('approve', $enrollments)
            ->assertHasNoActionErrors();

        foreach ($enrollments as $enrollment) {
            $this->assertSame(EnrollmentStatus::Active, $enrollment->fresh()->status);
        }
    }
}
