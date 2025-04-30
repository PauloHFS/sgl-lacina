<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // Import Str

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tecnologia>
 */
class TecnologiaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Simple list of potential technologies
        $techNames = [
            'PHP',
            'Laravel',
            'JavaScript',
            'React',
            'Vue.js',
            'Node.js',
            'Python',
            'Django',
            'Flask',
            'Java',
            'Spring',
            'C#',
            '.NET',
            'Ruby',
            'Rails',
            'Go',
            'Swift',
            'Kotlin',
            'TypeScript',
            'SQL',
            'PostgreSQL',
            'MySQL',
            'MongoDB',
            'Docker',
            'Kubernetes',
            'AWS',
            'Azure',
            'GCP',
            'HTML',
            'CSS',
            'TailwindCSS',
            'Bootstrap'
        ];

        return [
            'id' => Str::uuid(), // Generate UUID
            'nome' => $this->faker->unique()->randomElement($techNames), // Pick a unique name from the list
        ];
    }
}
