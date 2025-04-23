<?php

namespace App\Enums;

enum StatusVinculoProjeto: string
{
  case APROVADO = 'APROVADO';
  case PENDENTE = 'PENDENTE';
  case INATIVO = 'INATIVO';
}
