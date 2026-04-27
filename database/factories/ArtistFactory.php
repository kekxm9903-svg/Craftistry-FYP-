<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Artist;

class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'bio' => $this->faker->sentence(),
            'specialty' => $this->faker->word(),
        ];
    }
}
