<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory
use Illuminate\Database\Eloquent\Model;

class IntervenienteFinanceiro extends Model
{
    use HasFactory, HasUuids; // Use HasFactory and HasUuids

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public $incrementing = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function projetos()
    {
        return $this->hasMany(Projeto::class, 'interveniente_financeiro_id', 'id');
    }
}
