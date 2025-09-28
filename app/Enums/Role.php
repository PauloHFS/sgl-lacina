<?php

namespace App\Enums;

enum Role: string
{
    case COORDENADOR_MASTER = 'coordenador_master';
    case COORDENADOR = 'coordenador';
    case COLABORADOR = 'colaborador';
}
