<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User; // Import User model

class SchemaInfoTest extends TestCase
{
    use RefreshDatabase; // Ensures a clean database for each test

    /**
     * Test that the master tables info endpoint returns a successful response.
     */
    public function test_can_retrieve_master_tables_info(): void
    {
        // Create a user and act as that user for authentication
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/schema-info');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'tables',
                         'migrations' => [
                             '*' => ['name', 'batch']
                         ]
                     ]
                 ]);

        // Assert that common tables are present (order-independent)
        $response->assertJsonPath('data.tables', function (array $tables) {
            return in_array('users', $tables) &&
                   in_array('migrations', $tables) &&
                   in_array('employees', $tables) &&
                   in_array('departments', $tables) &&
                   in_array('attendances', $tables) &&
                   in_array('leave_requests', $tables) &&
                   in_array('cache', $tables) &&
                   in_array('cache_locks', $tables);
        });

        // Assert that common migrations are present (check by name)
        $response->assertJsonPath('data.migrations', function (array $migrations) {
            $migrationNames = collect($migrations)->pluck('name')->all();
            return in_array('2026_01_01_create_employees', $migrationNames) &&
                   in_array('2026_01_02_create_departments', $migrationNames) &&
                   in_array('2026_01_03_create_attendance', $migrationNames) &&
                   in_array('2026_01_04_create_leave_requests', $migrationNames) &&
                   in_array('2026_02_18_060304_create_cache_table', $migrationNames);
        });
    }

    /**
     * Test that unauthenticated access to the endpoint is denied.
     */
    public function test_unauthenticated_access_to_schema_info_is_denied(): void
    {
        $response = $this->getJson('/api/schema-info');

        $response->assertStatus(401); // Unauthorized
    }
}