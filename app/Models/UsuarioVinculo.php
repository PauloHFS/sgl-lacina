<?php

namespace App\Models;

use App\Enums\TipoVinculo; // Corrected: Use existing TipoVinculo enum
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto; // Added Status enum
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Added UUID trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class UsuarioVinculo extends Model
{
    /** @use HasFactory<\Database\Factories\UsuarioVinculoFactory> */
    use HasFactory, HasUuids; // Added HasUuids

    protected $table = 'usuario_vinculo';

    protected $primaryKey = 'id'; // Changed primary key to id
    public $incrementing = false; // Set incrementing to false for UUID
    protected $keyType = 'string'; // Set key type to string for UUID


    protected $fillable = [
        'id', // Added id
        'projeto_id',
        'usuario_id',
        'tipo_vinculo',
        'funcao',
        'status',
        'carga_horaria_semanal', // Added carga horaria
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'tipo_vinculo' => TipoVinculo::class,
        'funcao' => Funcao::class,
        'status' => StatusVinculoProjeto::class, // Added status cast
        'carga_horaria_semanal' => 'integer', // Added carga horaria cast
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class, 'projeto_id');
    }
}
