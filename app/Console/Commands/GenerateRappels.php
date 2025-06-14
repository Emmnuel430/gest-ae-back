<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RappelController; // Assure-toi que le contrôleur est importé

class GenerateRappels extends Command
{
    protected $signature = 'rappels:generate';
    protected $description = 'Génère automatiquement les rappels pour les étudiants';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $controller = new RappelController();
        $controller->generateRappels(); // Appelle la fonction pour générer les rappels
        $this->info('Rappels générés avec succès.');
    }
}
