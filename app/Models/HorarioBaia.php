<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioBaia extends Model
{
    /** @use HasFactory<\Database\Factories\HorarioBaiaFactory> */
    use HasFactory;

    protected $table = 'horario_baia';

    protected $primaryKey = ['baia_id', 'horario_id'];
    public $incrementing = false;

    protected $fillable = [
        'baia_id',
        'horario_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function baia()
    {
        return $this->belongsTo(Baias::class, 'baia_id');
    }

    public function horario()
    {
        return $this->belongsTo(Horarios::class, 'horario_id');
    }

    public function sala()
    {
        return $this->hasOneThrough(Salas::class, Baias::class, 'id', 'id', 'baia_id', 'sala_id');
    }
}
