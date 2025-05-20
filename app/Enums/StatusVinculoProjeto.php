<?php

namespace App\Enums;

enum StatusVinculoProjeto: string
{
  case APROVADO = 'APROVADO';
  case RECUSADO = 'RECUSADO';
  case PENDENTE = 'PENDENTE';
  case ENCERRADO = 'ENCERRADO';
}
