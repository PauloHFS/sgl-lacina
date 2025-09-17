<?php

namespace App\Enums;

enum StatusAusencia: string
{
    case PENDENTE = 'PENDENTE';
    case APROVADO = 'APROVADO';
    case REJEITADO = 'REJEITADO';
}
