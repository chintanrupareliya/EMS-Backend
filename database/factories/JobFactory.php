<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Job;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Job::class;
    public function definition(): array
    {
        return [
            'company_id' => 1, // Assuming company_id is a random number
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraph(),
            'salary' => rand(10000, 20000), // Random salary between $30,000 and $100,000
            'employment_type' => fake()->randomElement(['Full-time', 'Part-time', 'Contract', 'Freelance']),
            'required_experience' => [2, 5],
            'required_skills' =>
                ["JavaScript", "HTML", "CSS", "Data Analysis"]
            ,
            'posted_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'expiry_date' => fake()->dateTimeBetween('now', '+2 day'),
        ];
    }
}
