<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RappelImpController; // Assure-toi que le contrôleur est importé

class GenerateRappels extends Command
{
    protected $signature = 'rappels:generate';
    protected $description = 'Clôture les anciens rappels et génère les nouveaux.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $controller = new RappelImpController();
        // 1️⃣ Clôture des rappels dépassés
        $nbClosed = $controller->closeOldRappels();

        // 2️⃣ Génération des nouveaux rappels
        $response = $controller->generateRappels();

        $this->info("Rappels clôturés : $nbClosed");
        $this->info("Rappels générés avec succès.");
    }
}
