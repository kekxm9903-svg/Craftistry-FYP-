<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ClassModel;
use App\Models\Artist;

class ClassModelFactory extends Factory
{
    protected $model = ClassModel::class;

    public function definition()
    {
        return [
            'artist_id' => Artist::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(50, 300),
            'schedule' => $this->faker->dateTimeBetween('now', '+1 month'),
        ];
    }
}
