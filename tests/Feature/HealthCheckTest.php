<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the health check endpoint returns 'ok' when all dependencies are healthy.
     *
     * @return void
     */
    public function test_health_check_returns_ok_when_all_dependencies_are_healthy()
    {
        // Ensure DB and Cache are working as expected for this test
        DB::shouldReceive('connection')->andReturnSelf();
        DB::shouldReceive('getPdo')->andReturn(true);
        Cache::shouldReceive('put')->once()->andReturn(true);
        Cache::shouldReceive('get')->once()->andReturn('test_value');
        Cache::shouldReceive('forget')->once()->andReturn(true);

        $response = $this->getJson('/api/healthz');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'ok',
                     'dependencies' => [
                         'database' => ['status' => 'ok'],
                         'cache' => ['status' => 'ok'],
                     ],
                 ]);
    }

    /**
     * Test that the health check endpoint returns 'degraded' when the database connection fails.
     *
     * @return void
     */
    public function test_health_check_returns_degraded_when_database_fails()
    {
        // Mock DB connection to throw an exception
        DB::shouldReceive('connection')->andThrow(new Exception('Test DB connection failed'));

        // Ensure Cache is working for this test, even if DB fails
        Cache::shouldReceive('put')->once()->andReturn(true);
        Cache::shouldReceive('get')->once()->andReturn('test_value');
        Cache::shouldReceive('forget')->once()->andReturn(true);

        $response = $this->getJson('/api/healthz');

        $response->assertStatus(503)
                 ->assertJson([
                     'status' => 'degraded',
                     'dependencies' => [
                         'database' => ['status' => 'error', 'message' => 'Database connection failed: Test DB connection failed'],
                         'cache' => ['status' => 'ok'],
                     ],
                 ]);
    }

    /**
     * Test that the health check endpoint returns 'degraded' when the cache connection fails.
     *
     * @return void
     */
    public function test_health_check_returns_degraded_when_cache_fails()
    {
        // Ensure DB is working for this test, even if Cache fails
        DB::shouldReceive('connection')->andReturnSelf();
        DB::shouldReceive('getPdo')->andReturn(true);

        // Mock Cache operations to throw an exception
        Cache::shouldReceive('put')->once()->andThrow(new Exception('Test Cache write failed'));
        Cache::shouldNotReceive('get'); // Should not be called if put fails
        Cache::shouldNotReceive('forget'); // Should not be called if put fails

        $response = $this->getJson('/api/healthz');

        $response->assertStatus(503)
                 ->assertJson([
                     'status' => 'degraded',
                     'dependencies' => [
                         'database' => ['status' => 'ok'],
                         'cache' => ['status' => 'error', 'message' => 'Cache operation failed: Test Cache write failed'],
                     ],
                 ]);
    }
}