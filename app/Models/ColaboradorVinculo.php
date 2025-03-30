<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColaboradorVinculo extends Model
{
    /** @use HasFactory<\Database\Factories\ColaboradorVinculoFactory> */
    use HasFactory;

    protected $table = 'colaborador_vinculo';

    protected $primaryKey = ['docente_id', 'data_fim'];

    protected $fillable = [
        'colaborador_id',
        'tipo_vinculo',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }
}
