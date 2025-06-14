<?php

namespace Database\Seeders;

use App\Models\Moniteur;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MoniteurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('fr_FR');

        // Créer un tableau avec 5 'code' et 3 'conduite'
        $specialites = array_merge(array_fill(0, 5, 'code'), array_fill(0, 3, 'conduite'));
        shuffle($specialites); // Pour mélanger les spécialités

        foreach ($specialites as $specialite) {
            Moniteur::create([
                'nom' => $faker->lastName,
                'prenom' => $faker->firstName,
                'specialite' => $specialite,
                'num_telephone' => $faker->numerify('##########'),
                'num_telephone_2' => $faker->boolean ? $faker->numerify('##########') : null,
                'email' => $faker->unique()->safeEmail,
                'commune' => $faker->city,
            ]);
        }
    }

}
