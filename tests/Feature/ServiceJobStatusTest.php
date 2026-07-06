<?php

namespace Tests\Feature;

use App\Models\ServiceJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceJobStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function patchStatus(ServiceJob $job, string $status)
    {
        return $this->patchJson("/api/service-jobs/{$job->id}/status", ['status' => $status]);
    }

    public function test_full_valid_workflow_pending_to_delivered(): void
    {
        Sanctum::actingAs($this->admin);
        $job = ServiceJob::factory()->create();

        $this->patchStatus($job, 'in_progress')->assertOk();
        $this->patchStatus($job->fresh(), 'completed')->assertOk();
        $this->patchStatus($job->fresh(), 'delivered')->assertOk();

        $this->assertSame('delivered', $job->fresh()->status);
    }

    public function test_skipping_a_status_is_rejected(): void
    {
        Sanctum::actingAs($this->admin);
        $job = ServiceJob::factory()->create();

        $this->patchStatus($job, 'completed')
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertSame('pending', $job->fresh()->status);
    }

    public function test_moving_backwards_is_rejected(): void
    {
        Sanctum::actingAs($this->admin);
        $job = ServiceJob::factory()->create(['status' => 'in_progress']);

        $this->patchStatus($job, 'pending')->assertStatus(422);
    }

    public function test_mechanic_cannot_update_someone_elses_job(): void
    {
        $job = ServiceJob::factory()->create();
        Sanctum::actingAs(User::factory()->create(['role' => 'mechanic']));

        $this->patchStatus($job, 'in_progress')->assertStatus(422);
    }

    public function test_assigned_mechanic_can_advance_own_job(): void
    {
        $mechanic = User::factory()->create(['role' => 'mechanic']);
        $job = ServiceJob::factory()->create(['mechanic_id' => $mechanic->id]);
        Sanctum::actingAs($mechanic);

        $this->patchStatus($job, 'in_progress')->assertOk();
    }

    public function test_only_admin_can_cancel(): void
    {
        $mechanic = User::factory()->create(['role' => 'mechanic']);
        $job = ServiceJob::factory()->create(['mechanic_id' => $mechanic->id]);

        Sanctum::actingAs($mechanic);
        $this->patchStatus($job, 'cancelled')->assertStatus(422);

        Sanctum::actingAs($this->admin);
        $this->patchStatus($job, 'cancelled')->assertOk();
    }

    public function test_items_can_only_be_added_while_in_progress(): void
    {
        Sanctum::actingAs($this->admin);
        $job = ServiceJob::factory()->create();

        $this->postJson("/api/service-jobs/{$job->id}/items", ['name' => 'Part', 'cost' => 100])
            ->assertStatus(422);

        $this->patchStatus($job, 'in_progress')->assertOk();

        $this->postJson("/api/service-jobs/{$job->id}/items", ['name' => 'Part', 'cost' => 100])
            ->assertStatus(201);
    }

    public function test_mechanics_only_see_their_own_jobs(): void
    {
        $mechanic = User::factory()->create(['role' => 'mechanic']);
        ServiceJob::factory()->count(2)->create(['mechanic_id' => $mechanic->id]);
        ServiceJob::factory()->count(3)->create();

        Sanctum::actingAs($mechanic);

        $this->getJson('/api/service-jobs')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2);
    }
}
