<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Baias extends Model
{
    /** @use HasFactory<\Database\Factories\BaiasFactory> */
    use HasFactory;

    protected $table = 'baias';

    protected $fillable = [
        'id',
        'sala_id',
        'nome',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sala()
    {
        return $this->belongsTo(Salas::class, 'sala_id');
    }
    public function horarios()
    {
        return $this->belongsToMany(Horarios::class, 'horario_baia', 'sala_id', 'horario_id');
    }
}
