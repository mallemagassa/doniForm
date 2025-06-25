<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Program;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Program::class;


    public function definition(): array
    {
        return [
            //
            'title' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->paragraph(2),
            'region' => $this->faker->randomElement(['Bamako', 'Kayes', 'Sikasso', 'Mopti']),
            'start_date' => $this->faker->dateTimeBetween('now', '+2 months'),
            'end_date' => $this->faker->dateTimeBetween('+3 months', '+6 months'),
            'status' => $this->faker->randomElement(['open', 'closed', 'archived']),
            'created_by' => User::factory(), // ou un ID fixe si besoin
            'form_structure' => json_encode([
                ['label' => 'Nom complet', 'type' => 'text', 'required' => true],
                ['label' => 'Email', 'type' => 'email', 'required' => true],
                ['label' => 'Lettre de motivation', 'type' => 'textarea', 'required' => true],
                ]),
            'user_id' => User::factory(), // Associe le programme à un utilisateur
        ];
    }
}
