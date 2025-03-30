<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horarios extends Model
{
    /** @use HasFactory<\Database\Factories\HorariosFactory> */
    use HasFactory;

    protected $table = 'horarios';

    protected $fillable = [
        'id',
        'colaborador_id',
        'dia_semana',
        'horario_inicio',
        'horario_fim',
        'tipo',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime',
        'hora_fim' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }
    public function baias()
    {
        return $this->belongsToMany(Baias::class, 'horario_baia', 'horario_id', 'baia_id');
    }
}
