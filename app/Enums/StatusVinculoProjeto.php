<?php

namespace App\Enums;

enum StatusVinculoProjeto: string
{
  case ACEITO = 'ACEITO';
  case PENDENTE = 'PENDENTE';
  case INATIVO = 'INATIVO';
}
