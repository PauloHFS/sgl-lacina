<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import BelongsToMany

class Tecnologia extends Model
{
    use HasFactory, HasUuids; // Use HasFactory and HasUuids

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // Based on DBML, no timestamps

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
    ];

    /**
     * The users that belong to the tecnologia.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_tecnologia');
    }
}
