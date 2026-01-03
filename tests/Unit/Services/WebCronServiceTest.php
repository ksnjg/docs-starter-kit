<?php

namespace Tests\Unit\Services;

use App\Services\WebCronService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebCronServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebCronService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WebCronService;
    }

    public function test_is_async_supported_returns_boolean(): void
    {
        $result = $this->service->isAsyncSupported();

        $this->assertIsBool($result);
    }

    public function test_run_scheduler_returns_boolean(): void
    {
        $result = $this->service->runScheduler();

        $this->assertIsBool($result);
    }

    public function test_get_server_compatibility_returns_expected_structure(): void
    {
        $compatibility = $this->service->getServerCompatibility();

        $this->assertIsArray($compatibility);
        $this->assertArrayHasKey('proc_open', $compatibility);
        $this->assertArrayHasKey('max_execution_time', $compatibility);
        $this->assertArrayHasKey('php_version', $compatibility);
        $this->assertArrayHasKey('pending_jobs', $compatibility);
        $this->assertArrayHasKey('failed_jobs', $compatibility);
        $this->assertArrayHasKey('queue_driver', $compatibility);
    }

    public function test_proc_open_check_returns_expected_structure(): void
    {
        $compatibility = $this->service->getServerCompatibility();

        $this->assertIsArray($compatibility['proc_open']);
        $this->assertArrayHasKey('available', $compatibility['proc_open']);
        $this->assertArrayHasKey('reason', $compatibility['proc_open']);
        $this->assertIsBool($compatibility['proc_open']['available']);
    }

    public function test_max_execution_time_is_integer(): void
    {
        $compatibility = $this->service->getServerCompatibility();

        $this->assertIsInt($compatibility['max_execution_time']);
    }

    public function test_php_version_is_string(): void
    {
        $compatibility = $this->service->getServerCompatibility();

        $this->assertIsString($compatibility['php_version']);
        $this->assertEquals(PHP_VERSION, $compatibility['php_version']);
    }

    public function test_pending_jobs_returns_integer(): void
    {
        $compatibility = $this->service->getServerCompatibility();

        $this->assertIsInt($compatibility['pending_jobs']);
        $this->assertGreaterThanOrEqual(0, $compatibility['pending_jobs']);
    }

    public function test_failed_jobs_returns_integer(): void
    {
        $compatibility = $this->service->getServerCompatibility();

        $this->assertIsInt($compatibility['failed_jobs']);
        $this->assertGreaterThanOrEqual(0, $compatibility['failed_jobs']);
    }

    public function test_queue_driver_returns_configured_driver(): void
    {
        $compatibility = $this->service->getServerCompatibility();

        $this->assertIsString($compatibility['queue_driver']);
        $this->assertEquals(config('queue.default'), $compatibility['queue_driver']);
    }

    public function test_pending_jobs_counts_correctly(): void
    {
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['test' => 'data']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ]);

        $compatibility = $this->service->getServerCompatibility();

        $this->assertEquals(1, $compatibility['pending_jobs']);
    }

    public function test_failed_jobs_counts_correctly(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['test' => 'data']),
            'exception' => 'Test exception',
            'failed_at' => now(),
        ]);

        $compatibility = $this->service->getServerCompatibility();

        $this->assertEquals(1, $compatibility['failed_jobs']);
    }
}
