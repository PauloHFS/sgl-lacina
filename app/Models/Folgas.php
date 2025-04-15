<?php

namespace App\Models;

use App\Enums\StatusFolga;
use App\Enums\TipoFolga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folgas extends Model
{
    /** @use HasFactory<\Database\Factories\FolgasFactory> */
    use HasFactory;

    protected $table = 'folgas';

    protected $fillable = [
        'id',
        'usuario_id',
        'tipo',
        'status',
        'data_inicio',
        'data_fim',
        'justificativa',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tipo' => TipoFolga::class,
        'status' => StatusFolga::class,
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
