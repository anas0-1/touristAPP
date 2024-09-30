<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramFactory extends Factory
{
    // Define the associated model
    protected $model = Program::class;

    // Define the default values for the model attributes
    public function definition()
    {
        return [
            'user_id' => $this->faker->randomDigitNotNull(), // Random user ID (you may want to adjust this based on your User model)
            'name' => $this->faker->sentence(3), // A name with 3 words
            'description' => $this->faker->paragraph(2), // A short description
            'duration' => $this->faker->numberBetween(1, 30), // Duration in days
            'location' => $this->faker->city(), // Random city for location
            'price' => $this->faker->randomFloat(2, 50, 500), // Price between 50 and 500
            'starting_date' => $this->faker->date(), // Random date for starting date
            'created_at' => now(), // Created at timestamp
            'updated_at' => now(), // Updated at timestamp
        ];
    }
}
