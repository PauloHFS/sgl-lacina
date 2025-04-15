<?php

namespace App\Models;

use App\Enums\TipoHorario;
use Carbon\WeekDay;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horarios extends Model
{
    /** @use HasFactory<\Database\Factories\HorariosFactory> */
    use HasFactory;

    protected $table = 'horarios';

    protected $fillable = [
        'id',
        'usuario_id',
        'dia_semana',
        'horario_inicio',
        'horario_fim',
        'tipo',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'dia_semana' => WeekDay::class,
        'tipo' => TipoHorario::class,
        'hora_inicio' => 'datetime',
        'hora_fim' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'colaborador_id');
    }

    public function baias()
    {
        return $this->belongsToMany(Baias::class, 'horario_baia', 'horario_id', 'baia_id');
    }
}
