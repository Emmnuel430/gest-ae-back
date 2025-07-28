<?php

namespace Database\Seeders;

use App\Models\Etudiant;
use App\Models\Progression;
use App\Models\Log;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;

class EtudiantSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');

        foreach (range(1, 10) as $i) {
            $motif = $faker->randomElement(['permis', 'recyclage']);

            if ($motif === 'permis') {
                $categories = collect(['A', 'B', 'AB', 'BCDE', 'ABCDE'])->random();
                $mapped = match ($categories) {
                    'A' => ['A'],
                    'B' => ['B'],
                    'AB' => ['AB'],
                    'BCDE' => ['BCDE'],
                    'ABCDE' => ['ABCDE'],
                };
            } else {
                $mapped = [];
            }

            $hasReduction = $faker->boolean;
            $scolarite = $this->calculateScolarite($mapped, $hasReduction, $motif);

            $etudiant = Etudiant::create([
                'idMoniteur' => null,
                'idUser' => 4,
                'nom' => $faker->lastName,
                'prenom' => $faker->firstName,
                'dateNaissance' => Carbon::now()->subYears(rand(17, 30))->format('Y-m-d'),
                'lieuNaissance' => $faker->city,
                'commune' => $faker->city,
                'num_telephone' => $faker->numerify('##########'),
                'num_telephone_2' => $faker->boolean ? $faker->numerify('##########') : null,
                'nom_autoEc' => 'Patrimoine',
                'reduction' => $hasReduction,
                'type_piece' => $faker->randomElement(['CNI', 'Passeport', 'Permis', 'Attestation', 'Carte consulaire']),
                'num_piece' => strtoupper(Str::random(10)),
                'scolarite' => $scolarite,
                'montant_paye' => $faker->numberBetween(20000, $scolarite),
                'motif_inscription' => $motif,
                'categorie' => implode(',', $mapped),
            ]);

            Progression::create([
                'idEtudiant' => $etudiant->id,
                'etape' => 'inscription',
                'created_at' => now(),
            ]);
        }
    }

    private function calculateScolarite(array $categories, bool $hasReduction, string $motif): int
    {
        if ($motif === 'recyclage') {
            return 60000;
        }

        if ($hasReduction) {
            return 20000;
        }

        $categorySet = collect($categories);

        switch (true) {
            case $categorySet->count() === 1 && $categorySet->contains('A'):
                return 30000;
            case $categorySet->count() === 2 && $categorySet->contains('A') && $categorySet->contains('B'):
                return 100000;
            case $categorySet->count() === 4 && $categorySet->contains('B') && $categorySet->contains('C') && $categorySet->contains('D') && $categorySet->contains('E'):
                return 120000;
            case $categorySet->count() === 5:
                return 150000;
            default:
                return $categorySet->count() * 25000;
        }
    }
}
// This seeder creates 20 Etudiant records with random data.
// It uses the Faker library to generate realistic data for each field.
// The calculateScolarite method determines the tuition fee based on the categories selected,
// whether a reduction is applied, and the reason for registration.
// The categories are randomly selected from a predefined set, and the tuition fee is calculated accordingly.
// The seeder also includes logic to handle different scenarios, such as recycling and various category combinations.
// The generated data includes personal information such as name, date of birth, and contact details.
// The seeder can be run using the command: php artisan db:seed --class=EtudiantSeeder
// This will populate the Etudiant table with the generated data.