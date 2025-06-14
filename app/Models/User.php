<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Les attributs assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'pseudo',
        'password',
        'role',
    ];

    /**
     * Les attributs à cacher pour les tableaux.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public $timestamps = true; // Activer les timestamps

    /**
     * Les attributs avec type spécifié.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role' => 'boolean', // Cast du rôle en booléen
    ];

}
