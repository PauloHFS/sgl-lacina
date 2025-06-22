<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sala extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    public $keyType = 'string';

    protected $fillable = [
        'id',
        'nome',
        'descricao',
        'ativa',
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

    // Relacionamentos
    public function baias()
    {
        return $this->hasMany(Baia::class);
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativa', true);
    }

    public function scopeInativas($query)
    {
        return $query->where('ativa', false);
    }
}
