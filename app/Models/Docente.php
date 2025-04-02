<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 * Se mudar o modelo, lembrar de mudar também em: /resources/js/types/index.d.ts
 */
class Docente extends Model
{
    use HasFactory;

    protected $table = 'docentes';

    protected $fillable = [
        'id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
