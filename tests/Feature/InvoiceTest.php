<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\ServiceJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($this->admin);
    }

    private function completeJobWithItems(array $costs): ServiceJob
    {
        $job = ServiceJob::factory()->create();

        $this->patchJson("/api/service-jobs/{$job->id}/status", ['status' => 'in_progress']);

        foreach ($costs as $i => $cost) {
            $this->postJson("/api/service-jobs/{$job->id}/items", ['name' => "Part {$i}", 'cost' => $cost]);
        }

        $this->patchJson("/api/service-jobs/{$job->id}/status", ['status' => 'completed']);

        return $job->fresh();
    }

    public function test_invoice_is_auto_created_when_job_is_completed(): void
    {
        $job = $this->completeJobWithItems([1500, 2500]);

        $invoice = Invoice::where('service_job_id', $job->id)->first();

        $this->assertNotNull($invoice);
        $this->assertEquals(4000.0, (float) $invoice->parts_cost);
        $this->assertEquals(4000.0, (float) $invoice->total);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{4}$/', $invoice->invoice_no);
    }

    public function test_updating_labor_recomputes_total_server_side(): void
    {
        $job = $this->completeJobWithItems([1000]);
        $invoice = Invoice::where('service_job_id', $job->id)->first();

        $this->putJson("/api/invoices/{$invoice->id}", ['labor_cost' => 750])
            ->assertOk()
            ->assertJsonPath('data.total', 1750);
    }

    public function test_invoice_can_be_paid_once_only(): void
    {
        $job = $this->completeJobWithItems([1000]);
        $invoice = Invoice::where('service_job_id', $job->id)->first();

        $this->patchJson("/api/invoices/{$invoice->id}/pay")
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'paid');

        $this->patchJson("/api/invoices/{$invoice->id}/pay")->assertStatus(422);

        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_invoice_numbers_increment_within_the_year(): void
    {
        $this->completeJobWithItems([100]);
        $this->completeJobWithItems([200]);

        $numbers = Invoice::orderBy('id')->pluck('invoice_no');
        $year = now()->year;

        $this->assertSame("INV-{$year}-0001", $numbers[0]);
        $this->assertSame("INV-{$year}-0002", $numbers[1]);
    }

    public function test_mechanic_cannot_access_invoices(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'mechanic']));

        $this->getJson('/api/invoices')->assertStatus(403);
    }
}
