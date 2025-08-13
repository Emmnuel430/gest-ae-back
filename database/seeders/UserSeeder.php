<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('fr_FR');

        // Créer un dev en premier
        User::create([
            'nom' => $faker->lastName(),
            'prenom' => $faker->firstName(),
            'pseudo' => $faker->unique()->userName(),
            'password' => bcrypt('pass12345'),
            'role' => true,
            'dev' => true,
        ]);

        foreach (range(1, 9) as $index) {
            User::create([
                'nom' => $faker->lastName(),
                'prenom' => $faker->firstName(),
                'pseudo' => $faker->userName(),
                'password' => bcrypt('12345'), // mot de passe par défaut
                'role' => $faker->boolean(40), // 40% de chance que ce soit un admin (true)
            ]);
        }
    }
}
