<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salas extends Model
{
    /** @use HasFactory<\Database\Factories\SalasFactory> */
    use HasFactory;

    protected $table = 'salas';

    protected $fillable = [
        'id',
        'nome',
        'senha_porta',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function baias()
    {
        return $this->hasMany(Baias::class, 'sala_id');
    }
    public function horarios()
    {
        return $this->hasManyThrough(Horarios::class, Baias::class, 'sala_id', 'id', 'id', 'horario_id');
    }
}
