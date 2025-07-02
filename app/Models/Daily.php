<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Daily extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'usuario_id',
        'usuario_projeto_id',
        'data',
        'ontem',
        'observacoes',
        'hoje',
        'carga_horaria',
    ];

    protected $casts = [
        'data' => 'date',
        'carga_horaria' => 'integer',
    ];

    public $incrementing = false;
    public $keyType = 'string';

    /**
     * Get the value of the model's primary key.
     * Ensures the UUID is returned as a string.
     */
    public function getKey()
    {
        return (string) $this->getAttribute($this->getKeyName());
    }

    // Relacionamentos
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function usuarioProjeto(): BelongsTo
    {
        return $this->belongsTo(UsuarioProjeto::class, 'usuario_projeto_id');
    }

    // Scopes
    public function scopePorUsuario($query, string $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorData($query, string $data)
    {
        return $query->where('data', $data);
    }

    public function scopePorProjeto($query, string $usuarioProjetoId)
    {
        return $query->where('usuario_projeto_id', $usuarioProjetoId);
    }

    public function scopeEntreDatas($query, string $dataInicio, string $dataFim)
    {
        return $query->whereBetween('data', [$dataInicio, $dataFim]);
    }

    public function scopeRecentes($query, int $dias = 30)
    {
        return $query->where('data', '>=', now()->subDays($dias)->toDateString());
    }

    // Mutators & Accessors
    public function getCargaHorariaFormattedAttribute(): string
    {
        return $this->carga_horaria . 'h';
    }

    public function getDataFormattedAttribute(): string
    {
        return $this->data?->format('d/m/Y') ?? '';
    }

    // Helpers
    public function isToday(): bool
    {
        return $this->data?->isToday() ?? false;
    }

    public function isYesterday(): bool
    {
        return $this->data?->isYesterday() ?? false;
    }

    public function isPast(): bool
    {
        return $this->data?->isPast() ?? false;
    }
}
