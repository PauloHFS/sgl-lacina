<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Baia extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    public $keyType = 'string';

    protected $fillable = [
        'id',
        'nome',
        'descricao',
        'ativa',
        'sala_id',
    ];

    protected $casts = [
        'ativa' => 'boolean',
    ];

    public function uniqueIds()
    {
        return ['id'];
    }

    /**
     * Get the value of the model's primary key.
     * Ensures the UUID is returned as a string to avoid issues with NotificationFake.
     */
    public function getKey()
    {
        return (string) $this->getAttribute($this->getKeyName());
    }

    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }
    public function scopeAtivas($query)
    {
        return $query->where('ativa', true);
    }
    public function scopeInativas($query)
    {
        return $query->where('ativa', false);
    }
    public function scopePorSala($query, $salaId)
    {
        return $query->where('sala_id', $salaId);
    }
}
