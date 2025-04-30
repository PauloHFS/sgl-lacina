<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory
use Illuminate\Database\Eloquent\Model;

class AreaAtuacao extends Model
{
    use HasFactory, HasUuids; // Use HasFactory and HasUuids

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'areas_atuacao';

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
     * Get the users for the area de atuacao.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
