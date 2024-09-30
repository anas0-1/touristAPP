<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Program;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;  
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProgramControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user; 

    protected function setUp(): void
    {
        parent::setUp();
    
        // Create a personal access client for the tests
        $this->artisan('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
        ]);
    
        // Create roles if they don't exist
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
            $this->user = User::factory()->create();
        $this->user->assignRole('admin'); 
    }
    

    public function testCanCreateProgram()
    {
        Passport::actingAs($this->user);

        $response = $this->json('POST', '/api/v1/programs', [
            'user_id' => $this->user->id,
            'name' => 'Sample Program',
            'description' => 'A great program.',
            'duration' => 7,
            'location' => 'Paris',
            'price' => 199.99,
            'starting_date' => '2024-09-30',
            'activities' => [
                [
                    'name' => 'Sightseeing',
                    'description' => 'Visit famous landmarks.',
                    'time' => '10:00 AM',
                    'duration' => '2 hours',
                    'location' => 'Downtown',
                ]
            ]
        ], ['Accept' => 'application/json']);

        // Assert that the response status is 201 Created
        $response->assertStatus(201);

        // Check if the program was created in the database
        $this->assertDatabaseHas('programs', [
            'name' => 'Sample Program',
            'description' => 'A great program.',
            'duration' => 7,
            'location' => 'Paris',
            'price' => 199.99,
            'starting_date' => '2024-09-30',
        ]);
        
        // Check if the activity was created in the database
        $this->assertDatabaseHas('activities', [
            'name' => 'Sightseeing',
            'description' => 'Visit famous landmarks.',
            'time' => '10:00 AM',
            'duration' => '2 hours',
            'location' => 'Downtown',
            'program_id' => Program::where('name', 'Sample Program')->first()->id,
        ]);
    }
}
