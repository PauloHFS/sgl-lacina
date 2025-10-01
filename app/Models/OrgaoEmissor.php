<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class OrgaoEmissor extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orgaos_emissores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sigla',
        'nome',
    ];
}
