<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Docente;
use App\Models\Projeto;
use Thiagoprz\CompositeKey\HasCompositeKey;

class DocenteProjeto extends Model
{
    /** @use HasFactory<\Database\Factories\DocenteProjetoFactory> */
    use HasFactory, HasCompositeKey;

    protected $table = 'docente_projeto';

    protected $primaryKey = ['docente_id', 'projeto_id'];

    protected $fillable = [
        'docente_id',
        'projeto_id',
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function projeto()
    {
        return $this->belongsTo(Projeto::class, 'projeto_id');
    }
}
