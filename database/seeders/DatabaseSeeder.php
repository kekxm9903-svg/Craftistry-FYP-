<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Artist;
use App\Models\Order;
use App\Models\ClassModel;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create 10 users
        User::factory(10)->create();

        // Create 5 artists
        Artist::factory(5)->create()->each(function ($artist) {
            // Each artist has 2 classes
            ClassModel::factory(2)->create(['artist_id' => $artist->id]);
        });

        // Create 20 orders (randomly linking users and artists)
        Order::factory(20)->create();
    }
}
